<?php

namespace App\Http\Controllers;

use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\EventRepository;
use App\Traits\HandlePermissions;
use Illuminate\Http\Request;
use Inertia\Inertia;


class CabinCategoriesController extends Controller
{
    use HandlePermissions;
    protected EventRepositoryInterface $eventRepository;
    protected CabinCategoryInterface $cabinCategoryRepository;

    public function __construct(EventRepository $eventRepository,CabinCategoryRepository $cabinCategoryRepository){
        $this->eventRepository = $eventRepository;
        $this->cabinCategoryRepository = $cabinCategoryRepository;
    }

    public function index()
    {

        return $this->withPermission(['View Cabins'], function () {
            // Fetch the cabin categories and render the view
            $data = CabinCategory::all();
            return Inertia::render('Cabin/Index', [
                'data' => $data,
                'tab' => 'CATEGORIES',
            ]);
        });
    }

    public function create(Request $request)
    {
        
        return $this->withPermission(['Create Cabin'], function ($request) {
            $eventId = $request->route('id');
            $event = $this->eventRepository->find($eventId);
            return Inertia::render('CabinCategory/Create',['event' => $event]);

        },$request);
    }


    public function store(Request $request)
    {
        return $this->withPermission(['Create Cabin'], function (Request $request) {
            $rules = [
                'category_name' => 'required|string|max:255',
                'category_code' => 'required|string|max:5',
                'price' => 'required|numeric',
                'capacity' => 'required|integer',
                'description' => 'required|string',
                'category_type' => 'required|string',
                'display_order' => 'required|numeric',
                //'cruise_id' => 'required|numeric',
                'event_id' => 'required|numeric'
            ];
            $request->validate($rules);
            $cabinCategory = $this->cabinCategoryRepository->save($request->all());
            $event = $this->eventRepository->find($request->event_id);
            return Inertia::render('CabinCategory/Create',['event' => $event, 'cabin_category' => $cabinCategory]);
            
        },$request);
    }

    public function edit(Cabin $cabin)
    {
        return $this->withPermission(['Create Cabin'], function ($cabin) {
            return Inertia::render('CabinCategory/Edit', [
                'cabin' => $cabin
            ]);
        });
    }

    public function update(Request $request, Cabin $cabin) {}

    public function show(Cabin $cabin)
    {
        return Inertia::render('CabinCategory/View', ['cabin' => $cabin]);
    }

    public function destroy(Cabin $cabin)
    {
        $cabin->delete();
        return redirect()->route('cabincategory.index')->with('success', 'CabinCategory deleted successfully.');
    }
}
