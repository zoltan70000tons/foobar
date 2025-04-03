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
use App\Traits\ExceptionLogger;
use App\Enums\Permissions;


class CabinCategoriesController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

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
        try {
            return $this->withPermission([Permissions::ViewCabinCategories], function () {
                $data =[];
                $event_id = request()->route('id');
                $data = CabinCategory::where('event_id', '=', $event_id)->get()->toArray();
                
                return Inertia::render('Cabin/Index', [
                    'data' => array('data' => $data, 'event_id' => $event_id),
                    'tab' => 'CATEGORIES',
                ]);
            });
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create(Request $request)
    {
        try {
            return $this->withPermission([Permissions::CreateCabinCategories], function ($request) {
                $eventId = $request->route('id');
                $event = $this->eventRepository->find($eventId);
                return Inertia::render('CabinCategory/Create', [
                    'event' => $event,
                    'cruisers' => $this->cruisers
                ]);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }


    public function store(Request $request)
    {
       try {
        $this->withPermission([Permissions::CreateCabinCategories], function (Request $request) {
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
       } catch (\Exception $e) {
         $this->logException($e);
       }
    }

    public function edit($eventId, $cabCatId)
    {
       try {
        $event = $this->eventRepository->find($eventId);
        $category = $this->cabinCategoryRepository->find($cabCatId);
        return $this->withPermission([Permissions::EditCabinCategories], function ($event, $category) {
            return Inertia::render('CabinCategory/Edit', [
                'cabin_category' => $category,
                'event' => $event,
                'cruisers' => $this->cruisers
            ]);
        }, $event, $category);
       } catch (\Exception $e) {
        $this->logException($e);
       }
    }

    public function update(Request $request, $event, $cabCatId)
    {
        try {
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
            $this->withPermission([Permissions::EditCabinCategories], function ($request, $event, $category) {
                $this->cabinCategoryRepository->update($request->all(), $category->id);
                return redirect()->back()->with([
                    'message' => 'Cabin category updated successfully!',
                    'success' => true,
                ]);
            }, $request, $event, $category);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Error updating cabin category',
                'success' => false,
            ]);
            $this->logException($e);
        }
    }

    public function show(Request $request, $event, $catId)
    {
        try {
            $event = $this->eventRepository->find($event);
            $category = $this->cabinCategoryRepository->find($catId);
            return $this->withPermission([Permissions::ViewCabinCategories], function ($request, $event, $category) {
                $this->cabinCategoryRepository->update($request->all(), $category->id);
                return Inertia::render('CabinCategory/View', [
                    'cabin_category' => $category,
                    'event' => $event,
                    'cruisers' => $this->cruisers
                ]);
            }, $request, $event, $category);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function destroy($id, $catId)
    {
        try {
            $category = $this->cabinCategoryRepository->find($catId);
            $this->withPermission([Permissions::DeleteCabinCategories], function ($category, $id) {
                if ($category->cabins()->exists()) {
                    return redirect()->back()->with([
                        'message' => 'Cannot delete, there are cabins associated.',
                        'success' => false,
                    ]);
                }
                $category->delete();
                return redirect()->back()->with([
                    'message' => 'Cannot delete, there are cabins associatedCabin Category deleted successfully.',
                    'success' => true,
                ]);
            },  $category, $id);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'Error deleting category.',
                'success' => false,
            ]);
            $this->logException($e);
        }
    }
}
