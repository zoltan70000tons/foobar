<?php
// app/Support/GlobalLogger.php

namespace App\Support;

use App\Enums\GlobalLog\LogActionBooking;
use App\Enums\GlobalLog\LogActionCabin;
use App\Enums\GlobalLog\LogActionCustomer;
use App\Enums\GlobalLog\LogActionEvent;
use App\Enums\GlobalLog\LogActionUser;
use App\Models\Log;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use BackedEnum;

final class GlobalLogger
{
    /**
     * Insert a global log entry. If inside a transaction, defers the insert until commit.
     *
     * @param  string                 $action        
     * @param  'booking'|'customer'|'cabin' $relatedType Related object type
     * @param  string|int             $relatedId     object ID (stored as string)
     * @param  string                 $description   Description for humans
     * @param  array                  $payload       before/after data or other (JSON)
     * @param  string|null            $actorId       user UUID (if null => system)
     * @param  'agent'|'system'|null  $actorType     if null, deduced from $actorId
     * @param  DateTimeInterface|null $createdAt     event time (default now())
     *
     * @return Log|null returns the model only if inserted immediately; null if deferred or failed
     */
    public static function log(
        BackedEnum $action,
        string $relatedType,
        string|int $relatedId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?string $actorType = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        $relatedType = strtolower($relatedType);

        self::assertValidAction($action, $relatedType);

        $actorId   = $actorId ?? optional(Auth::user())->id;
        $actorType = $actorType ?? ($actorId ? 'agent' : 'system');

        $attrs = [
            'id'           => (string) Str::uuid(),
            'action'       => $action->value, // store string in DB
            'actor_type'   => $actorType,
            'actor_id'     => $actorId,
            'related_type' => $relatedType,
            'related_id'   => (string) $relatedId,
            'description'  => $description,
            'payload'      => $payload ?: null,
            'created_at'   => $createdAt ?? now(),
        ];

    // ...existing code...
        $insert = function () use ($attrs): ?Log {
            try {
                return Log::create($attrs);
            } catch (\Throwable $e) {
                logger()->warning('GlobalLogger insert failed', [
                    'error' => $e->getMessage(),
                    'attrs' => $attrs,
                ]);
                return null;
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit(static function () use ($insert) {
                $insert(); 
            });
            return null;
        }

    // ...existing code...
        return $insert();
    }

    public static function cabin(
        LogActionCabin $action,
        string|int $cabinId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        return self::log($action->value, 'cabin', $cabinId, $description, $payload, $actorId, null, $createdAt);
    }

    public static function booking(
        LogActionBooking $action,
        string $bookingId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        return self::log($action->value, 'booking', $bookingId, $description, $payload, $actorId, null, $createdAt);
    }

    public static function customer(
        LogActionCustomer $action,
        string $customerId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        return self::log($action->value, 'customer', $customerId, $description, $payload, $actorId, null, $createdAt);
    }

    public static function user(
        LogActionUser $action,
        string $userId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        return self::log($action->value, 'user', $userId, $description, $payload, $actorId, null, $createdAt);
    }

    public static function event(
        LogActionEvent $action,
        string $eventId,
        string $description,
        array $payload = [],
        ?string $actorId = null,
        ?DateTimeInterface $createdAt = null
    ): ?Log {
        return self::log($action->value, 'event', $eventId, $description, $payload, $actorId, null, $createdAt);
    }

    /**
     * Returns the allowed actions for a given related type from config/log_actions.php.
     */
    public static function allowedActionsFor(string $relatedType): array
    {
        return match (strtolower($relatedType)) {
            'cabin' => array_map(fn($case) => $case->value, LogActionCabin::cases()),
            'booking' => array_map(fn($case) => $case->value, LogActionBooking::cases()),
            'customer' => array_map(fn($case) => $case->value, LogActionCustomer::cases()),
            'user' => array_map(fn($case) => $case->value, LogActionUser::cases()),
            'event' => array_map(fn($case) => $case->value, LogActionEvent::cases()),
            default => [],
        };
    }

    /**
     * Throws InvalidArgumentException if $action is not in config/log_actions.php for $relatedType.
     */
    private static function assertValidAction(BackedEnum $action, string $relatedType): void
    {
        $allowed = self::allowedActionsFor($relatedType);

        if ($allowed && !in_array($action->value, $allowed, true)) {
            throw new InvalidArgumentException(
                "Invalid action '{$action->value}' for type '{$relatedType}'. Allowed: " . implode(', ', $allowed)
            );
        }
    }
}
