<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Tag;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\HandlePermissions;

class TagsController extends Controller {
    use HandlePermissions;
    protected EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }
    public function index(Request $request) {
        return $this->withPermission(
            [Permissions::ViewTags],
            function () use ($request) {
                $tags = Tag::orderBy('type')->orderBy('name')->get();
                return Inertia::render('Tags/Index', [
                    'tags' => $tags,
                ]);
            },
            $request,
        );
    }

    public function show(Tag $tag) {
        return $this->withPermission(
            [Permissions::ViewTags],
            function () use ($tag) {
                return Inertia::render('Tags/View', [
                    'tag' => $tag,
                ]);
            },
            $tag,
        );
    }

    public function create(Request $request) {
        return $this->withPermission(
            [Permissions::ViewTags],
            function () use ($request) {
                $events = $this->eventRepository->getAll();
                $tagTypes = ['Booking' => 'booking', 'Cabin' => 'cabin', 'Customer' => 'customer']; // Example tag types
                $tagTypesTransformed = array_map(
                    fn($name, $value) => ['name' => $name, 'value' => $value],
                    array_keys($tagTypes),
                    $tagTypes,
                );
                return Inertia::render('Tags/Create', [
                    'events' => $events,
                    'tagTypes' => $tagTypesTransformed,
                ]);
            },
            $request,
        );
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
            'entity' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        try {
            Tag::create([
                'name' => $validated['name'],
                'color' => $validated['color'],
                'type' => $validated['entity'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
            ]);
            return redirect()->route('tags.index')->with('flash', 'Tag created successfully.');
        } catch (\Throwable $th) {
            return redirect()
                ->route('tags.index')
                ->with('flash', 'Error creating tag: ' . $th->getMessage());
        }
    }

    public function edit(Tag $tag) {
        return $this->withPermission(
            [Permissions::EditTags],
            function () use ($tag) {
                return Inertia::render('Tags/Edit', [
                    'tag' => $tag,
                ]);
            },
            $tag,
        );
    }

    public function update(Request $request, Tag $tag) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
            'description' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $tag->update($validated);

        return redirect()->route('tags.index')->with('flash', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag) {
        $tag->delete();
        return redirect()->route('tags.index')->with('flash', 'Tag deleted successfully.');
    }
}
