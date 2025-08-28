<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagsController extends Controller
{
    protected EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }
    public function index()
    {
        $tags = Tag::orderBy('type')->orderBy('name')->get();
        return Inertia::render('Tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function show(Tag $tag)
    {
        return Inertia::render('Tags/View', [
            'tag' => $tag,
        ]);
    }

    public function create()
    {
        $events = $this->eventRepository->getAll();
        $tagTypes = ['Booking' => 'booking', 'Cabin' => 'cabin', 'Customer' => 'customer']; // Example tag types
        $tagTypesTransformed = array_map(
            fn($name, $value) => ['name' => $name, 'value' => $value],
            array_keys($tagTypes),
            $tagTypes
        );
        return Inertia::render('Tags/Create', [
            'events' => $events,
            'tagTypes' => $tagTypesTransformed,
        ]);
    }

    public function store(Request $request)
    {

        try {
            $newTag  = Tag::create(['name'=> $request->name,'color'=> $request->color,'type'=>$request->entity,'description' => $request->description]);
            return redirect()->route('tags.index')->with('flash', 'Tag created successfully.');
        } catch (\Throwable $th) {
            return redirect()->route('tags.index')->with('flash', 'Error creating tag: ' . $th->getMessage());
        }
        
    }   

    public function edit(Tag $tag)
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(Tag $tag)
    {
        $tag->update(request()->only(['name', 'color', 'description']));
        return redirect()->route('tags.index')->with('flash', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('tags.index')->with('flash', 'Tag deleted successfully.');
    }
}
