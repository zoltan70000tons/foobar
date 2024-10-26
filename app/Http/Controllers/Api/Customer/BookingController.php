<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\App;
use App\Models\Cabin;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;


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

  /**
   * Single event
   * 
   * 
   */
  public function showOne(Request $request, $id, $language = 'en')
  {
      App::setLocale($language);
  
      // Retrieve the event
      $event = Event::find($id);
  
      if (!$event) {
          return response()->json([
              'status' => 404, 
              'message' => __('event.no_event_found')
          ]);
      }
  
      // Check event status
      if (!in_array($event->status, ['pre-sale', 'public'])) {
          return response()->json([
              'status' => 403,
              'message' => __('event.no_event_found'),
          ]);
      }
  
      // Get purchase access information from the request
      $purchaseAccess = $request->get('purchase_access', false);
      $accessMessage = $request->get('access_message', '');
  
      return response()->json([
          'status' => 200,
          'event_status' => $event->status,
          'event' => $event,
          'purchase_access' => $purchaseAccess,
          'access_message' => $accessMessage,
      ]);
  }


  /**
   * Store a new booking
   * 
   * 
   */
  public function store(Request $request)
  {
    // 1. Validate the request
    $request->validate([
      'event_id' => 'required|integer',
      'ticket_type_id' => 'required|integer',
      'cabin_id' => 'required|integer',
      'user_id' => 'required|integer',
      'booking_price' => 'required|numeric',
    ]);

  }

}



