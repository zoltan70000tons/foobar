<?php

namespace App\Http\Controllers;

use App\Interfaces\CabinInterface;
use App\Models\Cabin;
use App\Repositories\CabinRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;

class CabinsController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    protected CabinInterface $cabinRepository;
    public function __construct(CabinRepository $cabinRepository){
        $this->cabinRepository = $cabinRepository;
    }
    public function index()
    {
        $event_id = request()->route('id');
        $data = $this->cabinRepository->getCategoriesAndCabins();
        return Inertia::render('Cabin/Index', [
            'tab' => 'ALL',
            'data'=> array('data' => $data, 'event_id' => $event_id),
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

    public function addTag(Request $request){
        $validatedData = $request->validate([
            'rows' => 'required|array|min:1',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:255',
        ]);
        $tags = $validatedData['tags'];
        $cabinIds = $validatedData['rows'];

        $cabins = Cabin::whereIn('id', $cabinIds)->get();

        foreach ($cabins as $cabin) {
            $existingTags = $cabin->tags ?? [];
            $updatedTags = array_unique(array_merge($existingTags, $tags));
            $updatedTags = array_values($updatedTags); 
            $cabin->tags = $updatedTags;
            $cabin->save();
        }

        return response()->json([
            'message' => 'Tags added succefully.',
        ], 200);
    }

    public function updateStatus(Request $request){
        $validatedData = $request->validate([
            'rows' => 'required|array|min:1',
            'status' => 'required|string',
        ]);
        $status = $validatedData['status'];
        $cabinIds = $validatedData['rows'];
        $cabins = Cabin::whereIn('id', $cabinIds)->get();
        foreach ($cabins as $cabin) {
            $cabin->status = $status;
            $cabin->save();
        }

        return response()->json([
            'message' => 'Status updated successfully',
        ], 200);
    }
}
