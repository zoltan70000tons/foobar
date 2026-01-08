<?php

namespace App\Http\Controllers;

use App\Enums\GlobalLog\LogActionCabin;
use App\Enums\Permissions;
use App\Enums\StatusCabin;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinSpec;
use App\Models\Log as LogModel;
use App\Models\Tag;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use App\Services\CabinInventoryIntegrityService;
use App\Support\GlobalLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Arr;
use App\Rules\CabinNumberIsAvailable;
use DB;
use Log;

class CabinsController extends Controller {
    use HandlePermissions;
    use ExceptionLogger;

    protected CabinInterface $cabinRepository;
    protected EventRepositoryInterface $eventRepository;
    protected CabinCategoryInterface $cabinCategoryRepository;
    protected BookingRepository $bookingRepository;

    public function __construct(
        CabinRepository $cabinRepository,
        EventRepository $eventRepository,
        CabinCategoryRepository $cabinCategoryRepository,
        BookingRepository $bookingRepository,
    ) {
        $this->cabinRepository = $cabinRepository;
        $this->eventRepository = $eventRepository;
        $this->cabinCategoryRepository = $cabinCategoryRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function index() {
        try {
            return $this->withPermission(
                [Permissions::ViewCabins],
                function () {
                    $event_id = request()->route('id');

                    if ($event_id == 'all') {
                        $events = $this->eventRepository->getAll();
                        return Inertia::render('Cabin/partials/Events', [
                            'events' => $events,
                        ]);
                    }

                    if (is_numeric($event_id)) {
                        $event = $this->eventRepository->find($event_id);
                        $cabins = $this->cabinRepository->getCategoriesAndCabins($event_id);
                        $categories = $this->cabinCategoryRepository->getCategoriesByEvent($event_id);
                        $tags = Tag::type('cabin')->get();
                        return Inertia::render('Cabin/Index', [
                            'cabins' => $cabins,
                            'categories' => $categories,
                            'event' => $event,
                            'tags' => $tags,
                        ]);
                    }
                },
                null,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create() {
        try {
            $event = $this->eventRepository->find(request()->route('id'));
            $cabinCategories = $this->cabinCategoryRepository->getAll();
            return $this->withPermission(
                [Permissions::CreateCabins],
                function ($event, $cabinCategories) {
                    return Inertia::render('Cabin/Create', [
                        'event' => $event,
                        'categories' => $cabinCategories,
                    ]);
                },
                $event,
                $cabinCategories,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function checkIfExists(Request $request) {
        $rules = [
            'cabin_number' => 'required|numeric',
            'event_id' => 'required|numeric',
        ];
        $validated = $request->validate($rules);
        try {
            $cabinSpec = CabinSpec::where('cabin_number', $validated['cabin_number'])
                ->whereHas('cabins', function ($query) use ($validated) {
                    $query->where('event_id', $validated['event_id']);
                })
                ->first();
            if ($cabinSpec) {
                return response()->json(['exists' => true, 'cabin_spec_id' => $cabinSpec->id]);
            } else {
                return response()->json(['exists' => false]);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'An error occurred while checking cabin availability.'], 500);
        }
    }

    public function store(Request $request) {
        $event_id = $request->route('id');
        $rules = [
            'status' => 'required|string|max:255',
            'cabin_number' => ['required', 'numeric', new CabinNumberIsAvailable()],
            'cabin_category_id' => 'required|numeric',
            'cabin_type_id' => 'required|numeric',
            'deck' => 'required|numeric',
            'location' => 'required|string',
            'connects_with' => 'nullable|numeric',
            'total_berths' => 'nullable|numeric',
            'lower_bed_type_1' => 'nullable|string',
            'lower_bed_type_2' => 'nullable|string',
            'accessible' => 'required|boolean',
            'balcony' => 'required|boolean',
            'obstructed_view' => 'required|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string',
            'upper_berths' => 'nullable|string',
            'notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ];
        $validated = $request->validate($rules);
        try {
            return $this->withPermission(
                [Permissions::CreateCabins],
                function ($event_id, $validated) {
                    //Arr::forget($validated, 'inventory');
                    $cabin = null;
                    $sanitized = Arr::map($validated, function ($value, $key) {
                        if (is_array($value)) {
                            return $value;
                        }
                        if (is_string($value)) {
                            return strip_tags(trim($value));
                        }

                        return $value;
                    });
                    $cabin = $this->cabinRepository->save($sanitized);
                    if (!$cabin) {
                        return redirect()->back()->with('error', 'Failed to create cabin. Please try again.');
                    }
                    return redirect()
                        ->route('cabins.edit', ['id' => $event_id, 'cabin_id' => $cabin->id])
                        ->with('message', 'Cabin created successfully.')
                        ->with('success', true);
                },
                $event_id,
                $validated,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function edit(Request $request) {
        try {
            $cabin = $this->cabinRepository->find($request->cabin_id);
            $event = $this->eventRepository->find(request()->route('id'));
            $relatedBookings = $this->bookingRepository->findByCabinId($event->id, $cabin->id);

            \Log::info('Related Bookings: ', ['bookings' => $relatedBookings]);
            $cabinCategories = $this->cabinCategoryRepository->getAll();
            $availableTags = Tag::type('cabin')->get();
            $logs = LogModel::query()
                ->select(
                    'logs.id',
                    'logs.created_at',
                    'logs.actor_type',
                    'logs.actor_id',
                    'users.username as actor_username',
                    'logs.action',
                    'logs.description',
                    'logs.related_type',
                    'logs.related_id',
                )
                ->leftJoin('users', 'users.id', '=', 'logs.actor_id')
                ->where('related_type', '=', 'cabin')
                ->where('related_id', '=', $cabin->id)
                ->get();

            return $this->withPermission(
                [Permissions::EditCabins, Permissions::ViewCabins],
                function ($event, $cabin, $cabinCategories, $availableTags, $logs, $relatedBookings) {
                    $shared = $this->cabinRepository->getSharedCabins($event->id, $cabin->cabin_spec_id);
                    return Inertia::render('Cabin/Edit', [
                        'cabin' => $cabin,
                        'event' => $event,
                        'categories' => $cabinCategories,
                        'shared' => $shared,
                        'availableTags' => $availableTags,
                        'relatedBookings' => $relatedBookings,
                        'logs' => $logs,
                    ]);
                },
                $event,
                $cabin,
                $cabinCategories,
                $availableTags,
                $logs,
                $relatedBookings,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function update(Request $request) {
        try {
            $cabin_id = $request->route('cabin_id');
            $event_id = $request->route('id');
            $rules = [
                'cabin_status' => 'required|string|max:255',
                'cabin_number' => [
                    'required',
                    'numeric',
                    //'unique:cabin_specs,cabin_number,' . $cabin_id . ',id'
                ],
                'cabin_category' => 'required|numeric',
                'cabin_type' => 'required|numeric',
                'deck' => 'required|numeric',
                'location' => 'required|string',
                'connects_with' => 'nullable|numeric',
                'total_berths' => 'nullable|numeric',
                'lower_bed_type_1' => 'nullable|string',
                'lower_bed_type_2' => 'nullable|string',
                'features' => 'required|array',
                'features.accessible' => 'required|boolean',
                'features.balcony' => 'required|boolean',
                'features.obstructed_view' => 'required|boolean',
                'tags' => 'nullable|array',
                'tags.*' => 'nullable|string',
                'upper_berths' => 'nullable|string',
                'notes' => 'nullable|string',
                'internal_notes' => 'nullable|string',
            ];
            $validated = $request->validate($rules);

            return $this->withPermission(
                [Permissions::EditCabins],
                function ($event_id, $cabin_id, $validated) {
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
                    return redirect()
                        ->route('cabins.edit', ['id' => $event_id, 'cabin_id' => $cabin_id])
                        ->with('success', 'Cabin updated successfully.');
                },
                $event_id,
                $cabin_id,
                $validated,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function show(Cabin $cabin) {
        try {
            return $this->withPermission(
                [Permissions::ViewCabins],
                function ($cabin) {
                    return Inertia::render('Cabins/View', ['cabin' => $cabin]);
                },
                $cabin,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function destroy(Cabin $cabin) {
        try {
            return $this->withPermission(
                [Permissions::DeleteCabins],
                function ($cabin) {
                    $cabin->delete();
                    return redirect()->route('cabins.index')->with('success', 'Cabin deleted successfully.');
                },
                $cabin,
            );
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    /**
     | --------------------------------------------------------------------------
     | CABIN INVENTORY INTEGRITY CHECK
     | --------------------------------------------------------------------------
     |
     | This function runs the cabin inventory integrity check
     | and sends the report via email.
     | if manual run, add global log with action CABIN_INVENTORY_CHECK_TRIGGERED
     |
     | @param Request $request
     | @param CabinInventoryIntegrityService $service
     |
     */
    public function runIntegrityCheck(Request $request, CabinInventoryIntegrityService $service) {
        // request id will be use to add for global log
        $reqEventId = (string) $request->route('id') ?? '';

        try {
            return $this->withPermission(
                [Permissions::EditCabinInventory],
                function () use ($service, $reqEventId) {
                    $report = $service->run();
                    $emailSent = $service->sendReport($report);

                    // GlobalLogger::log(
                    //     LogActionCabin::CABIN_INVENTORY_CHECK_TRIGGERED,
                    //     'event',
                    //     $reqEventId,
                    //     'Cabin inventory integrity check was manually triggered.',
                    //     [
                    //         'email_sent' => $emailSent,
                    //         'issues_found' => count($report['issues']),
                    //     ],
                    // );

                    return response()->json([
                        'report' => $report,
                        'email_sent' => $emailSent,
                    ]);
                },
                null,
            );
        } catch (\Exception $e) {
            $this->logException($e);
            return response()->json(['message' => 'Failed to run integrity check.'], 500);
        }
    }

    public function addTag(Request $request) {
        try {
            $validatedData = $request->validate([
                'rows' => 'required|array|min:1',
                'tags' => 'required|array|min:1',
                'tags.*' => 'string|max:255',
            ]);
            $tags = $validatedData['tags'];
            $cabinIds = $validatedData['rows'];

            $cabins = Cabin::whereIn('id', $cabinIds)->get();

            foreach ($cabins as $cabin) {
                $beforeTagIds = $cabin->tags()->pluck('tags.id')->all();

                $tag = Tag::type('cabin')->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $tags))->get();
                $cabin->tags()->sync($tag);

                $afterTagIds = $cabin->tags()->pluck('tags.id')->all();
                $addedIds = array_values(array_diff($afterTagIds, $beforeTagIds));
                $removedIds = array_values(array_diff($beforeTagIds, $afterTagIds));

                if ($addedIds || $removedIds) {
                    $addedNames = $addedIds ? Tag::whereIn('id', $addedIds)->pluck('name', 'id') : collect();
                    $removedNames = $removedIds ? Tag::whereIn('id', $removedIds)->pluck('name', 'id') : collect();

                    GlobalLogger::log(
                        LogActionCabin::TAG_UPDATED,
                        'cabin',
                        (string) $cabin->id,
                        trim(
                            sprintf(
                                'Tags updated%s%s',
                                $addedIds ? ' — added: ' . implode(', ', $addedNames->values()->all()) : '',
                                $removedIds ? ' — removed: ' . implode(', ', $removedNames->values()->all()) : '',
                            ),
                        ),
                        [
                            'before' => $beforeTagIds,
                            'after' => $afterTagIds,
                            'added' => $addedNames,
                            'removed' => $removedNames,
                        ],
                    );
                }
            }

            return redirect()
                ->back()
                ->with([
                    'message' => 'Tags updated successfully!',
                    'success' => true,
                ]);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()
                ->back()
                ->with([
                    'message' => 'Error updating tags',
                    'success' => false,
                ]);
        }
    }

    public function updateStatus(Request $request) {
        try {
            return $this->withPermission(
                [Permissions::EditCabins],
                function ($request) {
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

                    return redirect()
                        ->back()
                        ->with([
                            'message' => 'Status updated successfully!',
                            'success' => true,
                        ]);
                },
                $request,
            );
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()
                ->back()
                ->with([
                    'message' => 'Error updating status',
                    'success' => false,
                ]);
        }
    }

    public function createShared(Request $request) {
        try {
            $event_id = $request->route('id');
            $rules = [
                'cabin_id' => 'required|numeric',
                'cabin_category_id' => 'required|numeric',
            ];
            $validated = $request->validate($rules);
            $cabin = $this->cabinRepository->find($validated['cabin_id']);
            if (!$cabin) {
                return redirect()->route('cabins.index')->with('error', 'Cabin not found.');
            }
            $shared = $this->cabinRepository->createShared($cabin->id, $validated['cabin_category_id']);
            // dd($shared);
            return redirect()
                ->route('cabins.edit', ['id' => $event_id, 'cabin_id' => $cabin->id])
                ->with('success', 'Shared cabin created successfully.');
            // return $this->withPermission([Permissions::CreateCabins], function ($event_id, $cabin, $sharedCabins) {
            //     return Inertia::render('Cabin/CreateShared', [
            //         'event_id' => $event_id,
            //         'cabin' => $cabin,
            //         'shared' => $sharedCabins
            //     ]);
            // }, $event_id, $cabin, $sharedCabins);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function getData() {
        try {
            $eventId = request()->route('id');
            $perPage = request()->query('per_page', 10);
            $page = request()->query('page', 1);
            $code = request()->query('category_code', null);
            $name = request()->query('title', null);
            $data = $this->cabinRepository->getCategoriesAndCabins($eventId, $perPage, $page, $code, $name);
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            $this->logException($e);
            return response()->json(['data' => []], 500);
        }
    }
}
