<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Event::create($request->all());

        return redirect()->route('events.index')->with('success', 'Post created successfully.');
    }

    public function edit(Event $event)
    {
        return Inertia::render('Events/Edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post->update($request->all());

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function show()
    {
        return Inertia::render('Event/Create');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }
}
