<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\Event;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CabinsController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;
    protected CabinInterface $cabinRepository;
    protected EventRepositoryInterface $eventRepository;
    protected CabinCategoryInterface $cabinCategoryRepository;

    public function __construct(CabinRepository $cabinRepository, EventRepository $eventRepository, CabinCategoryRepository $cabinCategoryRepository)
    {
        $this->cabinRepository = $cabinRepository;
        $this->eventRepository = $eventRepository;
        $this->cabinCategoryRepository = $cabinCategoryRepository;
    }
    public function index()
    {
        $event_id = request()->route('id');
        $cabins = [];
        $categories = [];

        if ($event_id == 'all') {
            $events = $this->eventRepository->getAll();
            return Inertia::render('Cabin/partials/Events', [
                'events' => $events
            ]);
        }

        if (is_numeric($event_id)) {
            $event = $this->eventRepository->find($event_id);
            $cabins = $this->cabinRepository->getCategoriesAndCabins($event_id);
            $categories = $this->cabinCategoryRepository->getCategoriesByEvent($event_id);
            return Inertia::render('Cabin/Index', [
                'cabins' => $cabins,
                'categories' => $categories,
                'event' => $event

            ]);
        }
    }

    public function create()
    {
        return Inertia::render('Cabin/Create');
    }


    public function store(Request $request) {}

    public function edit(Request $request)
    {
        try {
            $cabin = $this->cabinRepository->find($request->cabin_id);
            $event = $this->eventRepository->find(request()->route('id'));
            $cabinCategories = $this->cabinCategoryRepository->getAll();
            return $this->withPermission([Permissions::EditCabins, Permissions::ViewCabins], function ($event, $cabin, $cabinCategories) {
                return Inertia::render('Cabin/Edit', [
                    'cabin' => $cabin,
                    'event' => $event,
                    'categories' => $cabinCategories
                ]);
            }, $event, $cabin, $cabinCategories);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function update(Request $request)
    {

        try {
            $cabin_id = $request->route('cabin_id');
            $event_id = $request->route('id');
            $rules = [
                'cabin_status'      => 'required|string|max:255',
                'cabin_number'      => [
                    'required',
                    'numeric',
                    'unique:cabins,cabin_number,' . $cabin_id . ',id'
                ],
                'cabin_category'    => 'required|numeric',
                'cabin_type'        => 'required|numeric',
                'deck'              => 'required|numeric',
                'location'          => 'required|string',
                'connects_with'     => 'nullable|numeric',
                'total_berths'      => 'nullable|numeric',
                'lower_bed_type_1'  => 'nullable|string',
                'lower_bed_type_2'  => 'nullable|string',
                'features' => 'required|array',
                'features.accessible' => 'required|boolean',
                'features.balcony' => 'required|boolean',
                'features.obstructed_view' => 'required|boolean',
                'tags' => 'nullable|array',
                'tags.*' => 'nullable|string',
                'upper_berths'      => 'nullable|string',
                'notes'             => 'nullable|string'
            ];
            $validated = $request->validate($rules);
            return $this->withPermission([Permissions::EditCabins], function ($event_id, $cabin_id, $validated) {
                Arr::forget($validated, 'inventory');
                $sanitized = Arr::map($validated, function ($value, $key) {
                    if (is_array($value)) {
                        return $value;
                    }
                    if (is_string($value)) {
                        return strip_tags(trim($value));
                    }

                    return $value;
                });
                $this->cabinRepository->update($sanitized, $cabin_id);
                return redirect()->route('cabins.edit', ['event_id' => $event_id, 'cabin_id' => $cabin_id])
                ->with('success', 'Cabin updated successfully.');

            }, $event_id, $cabin_id, $validated);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function show(Cabin $cabin)
    {
        return Inertia::render('Cabins/View', ['cabin' => $cabin]);
    }

    public function destroy(Cabin $cabin)
    {
        $cabin->delete();
        return redirect()->route('cabins.index')->with('success', 'Cabin deleted successfully.');
    }

    public function addTag(Request $request)
    {
        $validatedData = $request->validate([
            'rows' => 'required|array|min:1',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:255',
        ]);
        $tags = $validatedData['tags'];
        $cabinIds = $validatedData['rows'];

        $cabins = Cabin::whereIn('id', $cabinIds)->get();

        foreach ($cabins as $cabin) {
            // $existingTags = $cabin->tags ?? [];
            // $updatedTags = array_unique(array_merge($existingTags, $tags));
            // $updatedTags = array_values($updatedTags); 
            // $cabin->tags = $updatedTags;
            // $cabin->save();
            $cabin->tags = array_values($tags);
            $cabin->save();
        }

        return response()->json([
            'message' => 'Tags added succefully.',
        ], 200);
    }

    public function updateStatus(Request $request)
    {
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
