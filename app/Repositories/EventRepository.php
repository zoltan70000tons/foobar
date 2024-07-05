<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\EventResource;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventRepository implements EventRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAll()
    {
        return Event::all();
    }

    public function find($id)
    {
        return Event::find($id);
    }

    public function save($data)
    {
        DB::beginTransaction();

        try {
            $event = new Event();
            $event->name = $data['name'];
            $event->description = $data['description'];
            $event->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponserHelper::rollback($e);
        }
    }

    public function update($data, $id)
    {
        DB::beginTransaction();
        try {
            $event = Event::findOrFail($id);
            $event->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponserHelper::rollback($e);
        }
    }

    public function delete($id)
    {
    }
}
