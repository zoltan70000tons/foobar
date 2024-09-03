<?php

namespace App\Http\Controllers;

use App\Http\Resources\CruiserResource;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\Cruise;
use App\Models\Event;
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
    protected $cruisers;

    public function __construct(EventRepository $eventRepository, CabinCategoryRepository $cabinCategoryRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->cabinCategoryRepository = $cabinCategoryRepository;
        $cruisers = Cruise::all();
        $this->cruisers = CruiserResource::collection($cruisers);
    }

    public function index()
    {

        return $this->withPermission(['View Cabins'], function () {
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
            return Inertia::render('CabinCategory/Create', [
                'event' => $event,
                'cruisers' => $this->cruisers
            ]);
        }, $request);
    }


    public function store(Request $request)
    {
        $this->withPermission(['Create Cabin'], function (Request $request) {
            $rules = [
                'category_name' => 'required|string|max:255',
                'category_code' => 'required|string|max:5',
                'price' => 'required|numeric',
                'capacity' => 'required|integer',
                'description' => 'required|string',
                'category_type' => 'required|string',
                'display_order' => 'required|numeric',
                'cruise' => 'required|numeric',
                'event_id' => 'required|numeric'
            ];
            $request->validate($rules);
            $cabinCategory = $this->cabinCategoryRepository->save($request->all());
            $event = $this->eventRepository->find($request->event_id);
            return Inertia::render('CabinCategory/Create', ['event' => $event, 'cabin_category' => $cabinCategory, 'cruisers' => $this->cruisers]);
        }, $request);
    }

    public function edit($eventId, $cabCatId)
    {
        $event = $this->eventRepository->find($eventId);
        $category = $this->cabinCategoryRepository->find($cabCatId);
        return $this->withPermission(['Edit Cabin'], function ($event, $category) {
            return Inertia::render('CabinCategory/Edit', [
                'cabin_category' => $category,
                'event' => $event,
                'cruisers' => $this->cruisers
            ]);
        }, $event, $category);
    }

    public function update(Request $request, $event, $cabCatId)
    {
        $rules = [
            'category_name' => 'required|string|max:255',
            'category_code' => 'required|string|max:5',
            'price' => 'required|numeric',
            'capacity' => 'required|integer',
            'description' => 'required|string',
            'category_type' => 'required|string',
            'display_order' => 'required|numeric',
            'cruise' => 'required|numeric',
            'event_id' => 'required|numeric'
        ];
        $request->validate($rules);
        $event = $this->eventRepository->find($event);
        $category = $this->cabinCategoryRepository->find($cabCatId);
        $this->withPermission(['Edit Cabin'], function ($request, $event, $category) {
            $this->cabinCategoryRepository->update($request->all(), $category->id);
            return Inertia::render('CabinCategory/Edit', [
                'cabin_category' => $category,
                'event' => $event,
                'cruisers' => $this->cruisers
            ]);
        }, $request, $event, $category);
    }

    public function show(Request $request, $event, $catId)
    {
        $event = $this->eventRepository->find($event);
        $category = $this->cabinCategoryRepository->find($catId);
        return $this->withPermission(['View Cabins'], function ($request, $event, $category) {
            $this->cabinCategoryRepository->update($request->all(), $category->id);
            return Inertia::render('CabinCategory/View', [
                'cabin_category' => $category,
                'event' => $event,
                'cruisers' => $this->cruisers
            ]);
        }, $request, $event, $category);
    }

    public function destroy($id, $catId)
    {
        $category = $this->cabinCategoryRepository->find($catId);
        $this->withPermission(['Delete Cabin'], function ($category,$id) {
            $category->delete();
            return redirect()->route('cabins.categories',['id' => $id])->with('success', 'Cabin Category deleted successfully.');
        },  $category,$id);
    }
}
