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

        $data = $this->cabinRepository->getCategoriesAndCabins();
        return Inertia::render('Cabin/Index', [
            'tab' => 'ALL',
            'data'=> $data,
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
