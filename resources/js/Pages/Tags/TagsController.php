<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TagsController extends Controller
{
    public function index()
    {
        return Inertia::render('Tags/Index', [
            'tab' => 'TAGS',
        ]);
    }

    public function show()
    {
        return Inertia::render('Tags/Show', [
            'tab' => 'TAGS',
        ]);
    }

    public function create()
    {
        return Inertia::render('Tags/Create', [
            'tab' => 'TAGS',
        ]);
    }

    public function edit()
    {
        return Inertia::render('Tags/Edit', [
            'tab' => 'TAGS',
        ]);
    }

    public function update(Request $request, $id)
    {
        // Logic to update a tag
        return redirect()->route('tags.index')->with('flash', 'Tag updated successfully.');
    }

    public function destroy($id)
    {
        // Logic to delete a tag
        return redirect()->route('tags.index')->with('flash', 'Tag deleted successfully.');
    }
}
