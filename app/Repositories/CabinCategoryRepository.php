<?php

namespace App\Repositories;

use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\CabinCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CabinCategoryRepository implements CabinCategoryInterface
{
    function getAll() {}
    function find($id) {}

    function save(array $data) : ?CabinCategory
    {
        try {
            $files = $data['files'];
            foreach ($files as $file) {
                $path = $file->storePublicly('events/cabin-categories/' . $data['category_code'], 's3');
                $url = Storage::disk('s3')->url($path);
                $fileData[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => $url,
                    'date' => Carbon::now()->toDateTimeString()
                ];
            }
            $cat = new CabinCategory();
            $cat->category_name = $data['category_name'];
            $cat->category_type = $data['category_type'];
            $cat->category_code = $data['category_code'];
            $cat->capacity = $data['capacity'];
            $cat->description = $data['description'];
            $cat->price = $data['price'];
            $cat->display_order = $data['display_order'];
            //$cat->cruise_id =$data['cruise_id'];
            $cat->cruise_id = 1;
            $cat->event_id = $data['event_id'];
            $cat->images = json_encode($fileData);
            $cat->save();
            return $cat;
        } catch (\Exception $e) {
            dd($e->getMessage());
            return $e->getMessage();
        }
    }
    function update(array $data, $id) {}
    function delete($id) {}
}
