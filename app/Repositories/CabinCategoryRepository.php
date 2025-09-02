<?php

namespace App\Repositories;

use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Traits\ExceptionLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CabinCategoryRepository implements CabinCategoryInterface
{
    use ExceptionLogger;

    function getAll()
    {
        return CabinCategory::all();
    }

    function find($id)
    {
        return CabinCategory::find($id);
    }

    function getCategoriesByEvent($event_id)
    {
        return CabinCategory::with(['spec', 'cabins.cabinType'])
            ->where('event_id', '=', $event_id)
            ->whereHas('cabins', function ($query) {
                $query->whereIn('status', ['AVAILABLE', 'PARTIALLY_BOOKED', 'RESERVED']);
            })
            ->get()
            ->toArray();
    }

    function save(array $data): ?CabinCategory
    {
        try {
            $fileData = $this->uploadToS3($data);
            $cat = new CabinCategory();
            $cat->category_name = $data['category_name'];
            $cat->category_type = $data['category_type'];
            $cat->category_code = $data['category_code'];
            $cat->capacity = $data['capacity'];
            $cat->description = $data['description'];
            $cat->price = $data['price'];
            $cat->display_order = $data['display_order'];
            $cat->cruise_id = $data['cruise'];
            $cat->event_id = $data['event_id'];
            $cat->images = $fileData;
            $cat->save();

            return $cat;
        } catch (\Exception $e) {
            $this->logException($e);
            return null;
        }
    }
    function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $cat = CabinCategory::findOrFail($id);
            $catSpec = CabinCategorySpec::find($cat->cabin_category_spec_id);
            $images = $catSpec->images;
            if (!empty($data['remove']) && is_array($data['remove'])) {
                foreach ($data['remove'] as $filePath) {
                    $images = $this->removeImageByPath($images, $filePath);
                    if (Storage::disk('s3')->exists($filePath)) {
                        Storage::disk('s3')->delete($filePath);
                    } else {
                        Log::warning('Attempted to delete non-existing file from S3', ['path' => $filePath]);
                    }
                }
            }

            $images = $this->uploadToS3($data, $images);
            $cat->fill([
                'price' => $data['price'],
                'cruise_id' => $data['cruise'],
                'event_id' => $data['event_id'],
            ]);

            $catSpec->category_name = $data['category_name'];
            $catSpec->category_name = $data['category_type'];
            $catSpec->category_code = $data['category_code'];
            $catSpec->capacity = $data['capacity'];
            $catSpec->description = $data['description'];
            $catSpec->display_order = $data['display_order'];
            $catSpec->images = $images;
            $cat->save();
            $catSpec->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logException($e);
            return false;
        }
    }
    function delete($id) {}

    function removeImageByPath(array $images, string $filePath): array
    {
        return array_filter($images, function ($image) use ($filePath) {
            return $image['path'] !== $filePath;
        });
    }

    function uploadToS3($data, $fileData = [])
    {
        if (isset($data['files']) && is_array($data['files'])) {
            $files = $data['files'];
            foreach ($files as $file) {
                $path = $file->storePublicly('events/cabin-categories/' . $data['category_code'], 's3');
                $url = Storage::disk('s3')->url($path);
                $fileData[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => $url,
                    'date' => now()->toDateTimeString(),
                    'path' => $path,
                ];
            }
        }
        return $fileData;
    }
}
