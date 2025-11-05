<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Cart;
use App\Models\Cabin;
use App\Models\CabinCategory;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use App\Traits\MembershipAccess;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
  use MembershipAccess;

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
    $events = Event::with('adjustments')
      ->whereIn('status', ['PRE-SALE', 'PUBLIC'])
      ->get();

    if ($events->isEmpty()) {
      return response()->json(['message' => 'no events found']);
    }

    // return events if exist
    return response()->json([
      'events' => $events,
    ]);
  }

  /**
   * Single event
   *
   * @param Request $request
   */
  public function showOne(Request $request, $id, $language = 'en')
  {
    App::setLocale($language);

    // Retrieve the event with adjustments and presale periods
    $event = Event::with(['adjustments', 'presalePeriods.membershipType'])->find($id);

    if (!$event) {
      return response()->json([
        'status' => 404,
        'message' => __('event.no_event_found'),
      ]);
    }

    // check the event is past
    if ($event->start_date < Carbon::now()) {
      return response()->json(
        [
          'event' => [
            'purchase_access' => false,
            'access_message' => 'event_past',
          ],
        ],
        200
      );
    }

    // Check event status
    if (!in_array($event->status, ['PRE-SALE', 'PUBLIC'])) {
      return response()->json([
        'status' => 403,
        'message' => __('event.no_event_found'),
      ]);
    }

    // Get purchase access information from the request
    // $purchaseAccess = $request->get('purchase_access', false);
    // $accessMessage = $request->get('access_message', '');

    // check if the Auth
    $user = $request->user('api');
    // check if user is auth
    $customer = $user && $user->hasRole('Customer') ? $user : null;

    $membership = null;

    if ($customer) {
      $membership = $customer->membershipTypes->first() ?? null;
    }

    $access = $this->checkMembershipAccess($membership, null, $id);

    // $context = [];

    // if ($customer) {
    //   $cart = $customer->cart()->first();

    //   if ($cart && !empty($cart->cart_data)) {
    //     $categoryId = $cart->cart_data['cabin_category'] ?? null;
    //     $category = null;

    //     if ($categoryId) {
    //       $category = CabinCategory::with('spec')->where('id', $categoryId)->where('event_id', $id)->first();
    //     }

    //     // Build context
    //     $context['cabin'] = [
    //       'category_id' => $categoryId,
    //       'category_name' => $category?->category_name,
    //       'code' => $cart->cart_data['cabin_code'] ?? null,
    //       'capacity' => $cart->cart_data['cabin_capacity'] ?? null,
    //     ];

    //     // \Log::info('EventController showOne - context', ['context' => $context]);

    //     $context['cabin_category'] = $category;
    //   }
    // }

    // $event->adjustments->transform(function ($adjustment) use ($context) {
    //   $isApplicable = $adjustment->shouldApply($context);
    //   $adjustment->is_applicable = $isApplicable;

    //   if ($isApplicable && $adjustment->code === 'CHOOSE_YOUR_CABIN') {
    //     $adjustment->value = '0.00';
    //   }

    //   return $adjustment;
    // });

    return response()->json([
      'status' => 200,
      'event_status' => $event->status,
      'event' => $event,
      'purchase_access' => $access['status'],
      'access_message' => $access['message'] ?? null,
    ]);
  }
}
