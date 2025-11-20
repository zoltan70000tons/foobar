<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class TagRepository
{
    public function getTagsForBooking(Booking $booking)
    {
        return $booking->tags;
    }

    /** supported types → model*/
    private const MAP = [
        'booking'  => [Booking::class,  'bookings'],
        'cabin'    => [Cabin::class,    'cabins'],
        'user'     => [User::class,     'users'],
        'customer' => [User::class, 'customers'],
    ];

    /**
     * @param array<string> $tagNames  tag names (case-insensitive)
     * @param string $type             booking|cabin|user|customer
     * @param 'any'|'all' $mode        'any' = has any; 'all' = has all
     * @param int $perPage
     */
    public function searchByTags(array $tagNames, string $type, string $mode = 'any', int $perPage = 15): LengthAwarePaginator
    {
        $type = strtolower($type);
        $mode = strtolower($mode);

        if (!isset(self::MAP[$type])) {
            throw new InvalidArgumentException("Not supported type: {$type}");
        }
        if (!in_array($mode, ['any', 'all'], true)) {
            throw new InvalidArgumentException("Not supported mode: {$mode}");
        }

        [$model, $table] = self::MAP[$type];
        $names = array_values(array_unique(array_filter(array_map('trim', $tagNames))));
        if (empty($names)) {
            throw new InvalidArgumentException('Must to pass a tag name.');
        }

        /** @var Builder $q */
        $q = $model::query();
        $q->where(function (Builder $wrap) use ($names, $type, $table, $mode) {
            foreach ($names as $name) {
                $exists = function ($sub) use ($type, $table, $name) {
                    $sub->select(DB::raw(1))
                        ->from('taggings as tg')
                        ->join('tags as t', 't.id', '=', 'tg.tag_id')
                        ->whereColumn('tg.entity_id', DB::raw("({$table}.id)::text"))
                        ->where('tg.entity_type', $type)
                        ->where('t.type', $type)
                        ->whereRaw('LOWER(t.name) = LOWER(?)', [$name]);
                };

                if ($mode === 'all') {
                    $wrap->whereExists($exists);
                } else {
                    $wrap->orWhereExists($exists);
                }
            }
        });
        $q->with(['tags' => fn($t) => $t->where('tags.type', $type)]);
        return $q->paginate($perPage)->withQueryString();
    }

    public function attachToEntity(Tag $tag, string $entityType, string $entityId): void
    {
        $entityType = strtolower($entityType);
        if (!isset(self::MAP[$entityType])) {
            throw new InvalidArgumentException("Not supported Type: {$entityType}");
        }

        $tag->taggings()->firstOrCreate([
            'entity_type' => $entityType,
            'entity_id'   => (string)$entityId,
        ]);
    }

    public function detachFromEntity(Tag $tag, string $entityType, string $entityId): void
    {
        $entityType = strtolower($entityType);
        if (!isset(self::MAP[$entityType])) {
            throw new InvalidArgumentException("Not supported Type: {$entityType}");
        }

        $tag->taggings()->where([
            'entity_type' => $entityType,
            'entity_id'   => (string)$entityId,
        ])->delete();
    }
    
    public function getAll(string $type = 'booking')
    {
        $q = Tag::query();
        if ($type !== null) {
            $type = strtolower($type);
            if (!isset(self::MAP[$type])) {
                throw new InvalidArgumentException("Not supported Type: {$type}");
            }
            $q->where('type', $type);
        }

        return $q->get();
    }
}
