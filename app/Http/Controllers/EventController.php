<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;

class EventController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    public function index(Request $request)
    {
        try {
            return $this->withPermission([Permissions::ViewPermissions], function ($request) {
                $events = Event::all();
                return Inertia::render('Event/Index', [
                    'events' => $events
                ]);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create()
    {
        return Inertia::render('Event/Create');
    }


    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'destination' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:500',
            'start_date' => 'nullable|date_format:Y/m/d',
            'end_date' => 'nullable|date_format:Y/m/d|after_or_equal:start_date',
            'status' => 'required|string'
        ];
        $request->validate($rules);
        try {
            return $this->withPermission([Permissions::CreateEvents], function ($request) {
                $path = $request->file('image')->storePublicly('events', 's3');
                $publicPath = Storage::url($path);
                $event = new Event();
                $event->name = $request->name;
                $event->description = $request->description;
                $event->image = $publicPath;
                $event->status = $request->status;
                $event->organization_id = 1;
                $event->address = $request->destination;
                $event->start_date = $request->start_date ? $request->start_date : null;
                $event->end_date = $request->end_date ? $request->end_date : null;
                $event->save();

                return redirect()->route('events.index')->with('flash', 'Event created successfully.');
            }, $request);
        } catch (\Exception $e) {
            return redirect()->route('events.index')->with('error', 'Problem creating event.');
        }
    }

    public function edit(Event $event)
    {
        return Inertia::render('Event/Edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {

        try {

            if ($request->hasFile('image')) {
                $path = $request->file('image')->storePublicly('events', 's3');
                $publicPath = Storage::url($path);
                $event->image = $publicPath;
            }

            $event->name = $request->name;
            $event->description = $request->description;
            $event->address = $request->destination;
            $event->start_date = $request->start_date ? $request->start_date : null;
            $event->end_date = $request->end_date ? $request->end_date : null;
            $event->status = $request->status;

            $event->save();

            return redirect()->route('events.edit', $event->id)
                ->with('success', 'Event updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('events.edit', $event->id)->with('error', 'Problem updating event.');
        }
    }

    public function show(Event $event)
    {
        try {
            return $this->withPermission([Permissions::ViewEvents], function ($event) {
                return Inertia::render('Event/View', ['event' => $event]);
            }, $event);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function destroy(Event $event)
    {
        try {
            return $this->withPermission([Permissions::DeleteEvents], function ($event) {
                $event->delete();
                return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
            }, $event);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }
}
