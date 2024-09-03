<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;

class BookingController extends Controller
{

  /**
   * Show only events that are in pre-sale or public status
   * 
   * If no events are found, return a message
   * Pre-sale: Only logged-in users whose membership status matches one of the entries in the new table above can book. The current date must also be within the range.
   * Public: Everyone can book.
   * Closed: Event is shown to the public, but no longer taking booking requests
   * Draft: The event exists only for admin users internally.
   * 
   */
  public function show()
  {

    // check if event exist and get only if pre-sale or public
    $events = Event::where('status', 'pre-sale')
      ->orWhere('status', 'public')
      ->get();

    if ($events->isEmpty()) {
      return response()->json(['message' => 'no events found']);
    }

    // return events if exist
    return response()->json([
      'events' => $events
    ]);
  }

  // show a single event
  public function showOne($id)
  {

    // return event by id if exist
    $event = Event::find($id);

    if (!$event) {
      return response()->json(['message' => 'event not found']);
    }

    return response()->json([
      'event' => $event
    ]);
  }

  // test
  public function store(Request $request): JsonResponse
  {
    return response()->json(['message' => 'test message']);
  }
}
