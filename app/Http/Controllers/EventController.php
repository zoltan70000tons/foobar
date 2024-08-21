<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return Inertia::render('Event/Index', [
            'events' => $events
        ]);
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
            $file = $request->file('image');
            $path = $file->store('public/images');
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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'destination' => 'required|string',
            //'start_date' => 'nullable|date_format:Y/m/d',
            'start_date' => ['nullable', new ValidDateFormat],
            'end_date' => ['nullable', new ValidDateFormat, 'after_or_equal:start_date'],
            'status' => 'required|string'
        ];

        $validated = $request->validate($rules);

        try {
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($event->image) {
                    Storage::delete(parse_url($event->image, PHP_URL_PATH));
                }

                $file = $request->file('image');
                $path = $file->store('images');
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
        return Inertia::render('Event/View',['event' => $event]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
