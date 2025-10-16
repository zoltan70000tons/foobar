<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\Event;
use App\Models\MembershipType;
use App\Models\PresalePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;

class EventController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;
  public function index(Request $request)
  {
    try {
      return $this->withPermission(
        [Permissions::ViewEvents],
        function ($request) {
          $events = Event::all();
          return Inertia::render('Event/Index', [
            'events' => $events,
          ]);
        },
        $request
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function create()
  {
    $membershipTypes = MembershipType::all();

    $membershipWithPeriods = $membershipTypes->map(function ($membershipType) {
      return [
        'membership_type' => $membershipType,
        'presale_period' => null,
      ];
    });

    return Inertia::render('Event/Create', [
      'membership_presale_periods' => $membershipWithPeriods,
    ]);
  }

  public function store(Request $request)
  {
    $rules = [
      'name' => 'required|string|max:255',
      'description' => 'required|string',
      'destination' => 'required|string',
      'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:500',
      'start_date' => 'nullable|date_format:Y/m/d',
      'end_date' => 'nullable|date_format:Y/m/d|after_or_equal:start_date',
      'status' => 'required|string',
    ];
    $request->validate($rules);

    $membershipPresalePeriods = json_decode($request->membership_presale_periods, true);

    return $this->withPermission(
      [Permissions::CreateEvents],
      function ($request, $membershipPresalePeriods) {
        DB::beginTransaction();

        try {
          $path = $request->file('image')->storePublicly('events', 's3');
          $publicPath = Storage::url($path);
          $event = new Event();
          $event->name = $request->name;
          $event->description = $request->description;
          $event->image = $publicPath;
          $event->status = $request->status;
          $event->organization_id = 1;
          $event->address = $request->destination;
          $event->start_date = $request->start_date ? $request->start_date : null;
          $event->end_date = $request->end_date ? $request->end_date : null;
          $event->save();

          foreach ($membershipPresalePeriods as $item) {
            $membershipTypeId = $item['membership_type']['id'];
            $presaleData = $item['presale_period'] ?? null;

            if (!$presaleData) {
              // No presale period data sent for this membership type, skip it
              continue;
            }

            PresalePeriod::create([
              'id' => Str::uuid()->toString(),
              'event_id' => $event->id,
              'membership_type_id' => $membershipTypeId,
              'start_date' => $presaleData['start_date'],
              'end_date' => $presaleData['end_date'],
            ]);
          }

          DB::commit();

          return redirect()->route('events.index')->with('flash', 'Event created successfully.');
        } catch (\Exception $e) {
          DB::rollBack();
          $this->logException($e);
          return redirect()->route('events.index')->with('error', 'Problem creating event.');
        }
      },
      $request,
      $membershipPresalePeriods
    );
  }

  public function edit(Event $event)
  {
    return $this->withPermission(
      [Permissions::EditEvents],
      function ($event) {
        $membershipTypes = MembershipType::all();
        $presalePeriods = PresalePeriod::where('event_id', $event->id)
          ->get()
          ->keyBy('membership_type_id');

        $membershipWithPeriods = $membershipTypes->map(function ($membershipType) use ($presalePeriods) {
          return [
            'membership_type' => $membershipType,
            'presale_period' => $presalePeriods->get($membershipType->id) ?? null,
          ];
        });

        return Inertia::render('Event/Edit', [
          'event' => $event,
          'membership_presale_periods' => $membershipWithPeriods,
        ]);
      },
      $event
    );
  }

  public function update(Request $request, Event $event)
  {
    return $this->withPermission(
      [Permissions::EditEvents],
      function ($request, $event) {
        DB::beginTransaction();

        try {
          if ($request->hasFile('image')) {
            $path = $request->file('image')->storePublicly('events', 's3');
            $publicPath = Storage::url($path);
            $event->image = $publicPath;
          }

          $event->name = $request->name;
          $event->description = $request->description;
          $event->address = $request->destination;
          $event->start_date = $request->start_date ? $request->start_date : null;
          $event->end_date = $request->end_date ? $request->end_date : null;
          $event->status = $request->status;

          $event->save();

          $membershipPresalePeriods = json_decode($request->membership_presale_periods, true);

          foreach ($membershipPresalePeriods as $item) {
            $membershipTypeId = $item['membership_type']['id'];
            $presaleData = $item['presale_period'] ?? null;

            // Find existing presale_period for this event and membership_type
            $presalePeriod = PresalePeriod::query()
              ->where('event_id', $event->id)
              ->where('membership_type_id', $membershipTypeId)
              ->first();

            if ($presalePeriod) {
              if (!$presaleData) {
                // Exists in DB but no data sent → delete it
                $presalePeriod->delete();
                continue;
              }
              // Exists and data sent → update
              $presalePeriod->update([
                'start_date' => $presaleData['start_date'],
                'end_date' => $presaleData['end_date'],
              ]);
            } else {
              if (!$presaleData) {
                // Does not exist and no data sent → skip
                continue;
              }
              // Does not exist but data sent → create

              PresalePeriod::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'event_id' => $event->id,
                'membership_type_id' => $membershipTypeId,
                'start_date' => $presaleData['start_date'],
                'end_date' => $presaleData['end_date'],
              ]);
            }
          }

          DB::commit();

          return redirect()
            ->route('events.edit', $event->id)
            ->with('success', 'Event updated successfully.');
        } catch (\Exception $e) {
          DB::rollBack();
          return redirect()
            ->route('events.edit', $event->id)
            ->with('error', 'Problem updating event.');
        }
      },
      $request,
      $event
    );
  }

  public function show(Event $event)
  {
    try {
      return $this->withPermission(
        [Permissions::ViewEvents],
        function ($event) {
          $event->load(['presalePeriods.membershipType']);

          return Inertia::render('Event/View', ['event' => $event]);
        },
        $event
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function destroy(Event $event)
  {
    try {
      return $this->withPermission(
        [Permissions::DeleteEvents],
        function ($event) {
          $event->delete();
          return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
        },
        $event
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }
}
