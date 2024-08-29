<?php

namespace App\Http\Controllers;

use App\Models\Cabin;
use App\Models\CabinCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;


class CabinCategoriesController extends Controller
{
    public function index()
    {
        $data = CabinCategory::all();
         return Inertia::render('Cabin/Index', [
            'data' => $data,
            'tab' => 'CATEGORIES',
         ]);
    }

    public function create()
    {
       return Inertia::render('CabinCategory/Create');
    }


    public function store(Request $request)
    {

      
    }

    public function edit(Cabin $cabin)
    {
        return Inertia::render('CabinCategory/Edit', [
            'cabin' => $cabin
        ]);
    }

    public function update(Request $request, Cabin $cabin)
    {
       
    }

    public function show(Cabin $cabin)
    {
         return Inertia::render('CabinCategory/View',['cabin' => $cabin]);
    }

    public function destroy(Cabin $cabin)
    {
        $cabin->delete();
        return redirect()->route('cabincategory.index')->with('success', 'CabinCategory deleted successfully.');
    }
}
