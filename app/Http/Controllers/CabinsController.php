<?php

namespace App\Http\Controllers;

use App\Models\Cabin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;

class CabinsController extends Controller
{
    public function index()
    {
        return Inertia::render('Cabin/Index', [
            'tab' => 'ALL',
        ]);
    }

    public function create()
    {
       return Inertia::render('Cabin/Create');
    }


    public function store(Request $request)
    {

      
    }

    public function edit(Cabin $cabin)
    {
        return Inertia::render('Cabin/Edit', [
            'cabin' => $cabin
        ]);
    }

    public function update(Request $request, Cabin $cabin)
    {
       
    }

    public function show(Cabin $cabin)
    {
         return Inertia::render('Cabins/View',['cabin' => $cabin]);
    }

    public function destroy(Cabin $cabin)
    {
        $cabin->delete();
        return redirect()->route('cabins.index')->with('success', 'Cabin deleted successfully.');
    }
}
