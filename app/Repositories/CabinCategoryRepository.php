<?php

namespace App\Repositories;

use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\CabinCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CabinCategoryRepository implements CabinCategoryInterface
{
    function getAll()
    {
        return CabinCategory::all();
    }

    function find($id)
    {
        return CabinCategory::find($id);
    }

    function getCategoriesByEvent($event_id){
        return CabinCategory::where('event_id', '=', $event_id)->get()->toArray();
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
            dd($e->getMessage());
            return null;
        }
    }
    function update(array $data, $id)
    {
        try {
            $cat = CabinCategory::findOrFail($id);
            $images = $cat->images;
            if (!empty($data['remove']) && is_array($data['remove'])) {
                foreach ($data['remove'] as $filePath) {
                    $images =  $this->removeImageByPath($images, $filePath);
                    if (Storage::disk('s3')->exists($filePath)) {
                        Storage::disk('s3')->delete($filePath);
                    } else {
                        Log::warning('Attempted to delete non-existing file from S3', ['path' => $filePath]);
                    }
                }
            }

            $images = $this->uploadToS3($data, $images);
            $cat->fill([
                'category_name' => $data['category_name'],
                'category_type' => $data['category_type'],
                'category_code' => $data['category_code'],
                'capacity' => $data['capacity'],
                'description' => $data['description'],
                'price' => $data['price'],
                'display_order' => $data['display_order'],
                'cruise_id' => $data['cruise'],
                'event_id' => $data['event_id'],
                'images' => $images
            ]);
            $cat->save();
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
    function delete($id) {
        
    }

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
                    'path' => $path
                ];
            }
        }
        return $fileData;
    }
}
