<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\EventResource;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Event;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\DB;

class EventRepository implements EventRepositoryInterface
{

    use HandlePermissions;
    use ExceptionLogger;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAll()
    {
        return Event::orderBy('start_date', 'desc')->get();
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

    public function listMenu(){
        try {
            return Event::all();
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }
}
