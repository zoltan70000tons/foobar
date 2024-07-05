<?php

namespace App\Http\Controllers\Api;

use App\Classes\ApiResponserHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Event\SaveEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Interfaces\EventRepositoryInterface;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class EventController extends Controller
{

    private EventRepositoryInterface $eventRepositoryInterface;

    public function __construct(EventRepository $eventRepositoryInterface)
    {
        $this->eventRepositoryInterface = $eventRepositoryInterface;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->eventRepositoryInterface->getAll();
        return ApiResponserHelper::sendResponse(EventResource::collection($data), '', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveEventRequest $request)
    {
        $data = [
            'name' => $request->name,
            'description' => $request->description
        ];
        $event = $this->eventRepositoryInterface->save($data);
        return ApiResponserHelper::sendResponse(new EventResource($event), 'Event created successfull', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = $this->eventRepositoryInterface->find($id);
        return ApiResponserHelper::sendResponse(new EventResource($event), 'Listing all events', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, string $id)
    {
        $data = $request->all();
        $event = $this->eventRepositoryInterface->update($data,$request->id);
        return ApiResponserHelper::sendResponse(new EventResource($event), 'Event updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
