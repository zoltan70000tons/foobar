<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Traits\MembershipAccess;
use App\Models\Event;

class MembershipSales
{

  use MembershipAccess;

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next): Response
  {

    $language = $request->query('language', 'en');
    $id = $request->route('id') ?? null;

    App::setLocale($language);

    $event = Event::find($id);

    if ($event->status !== 'pre-sale' && $event->status !== 'public') {
      return response()->json([
        'message' => __('event.no_event_found'),
        'status' => false,
        'event_info' => null,
      ], 404);
    }

    // Check if the current date is after the "Everyone" access date
    if ($this->checkMembershipAccess(null, null, $id)['status'] === true) {
      return $next($request);
    }

    // Get the authenticated customer by guard
    if (!Auth::guard('customer')->check()) {
      return response()->json([
        'message' => __('auth.no_access_to_sales_everyone'),
        'status' => false,
        'event_info' => $this->getEventInfo($id),
      ], 403);
    }

    $customer = Auth::guard('customer')->user();
    $membership = $customer->membershipTypes->first();

    $access = $this->checkMembershipAccess($membership, null, $id);

    if ($access['status'] === false) {
      return response()->json([
        'message' => $access['message'],
        'status' => false,
        'event_info' => $this->getEventInfo($id),
      ], 403);
    }

    return $next($request);
  }

  /**
   * Get event information
   * 
   * @param  int  $id
   * @return array
   */
  protected function getEventInfo($id)
  {
    $event = Event::find($id);

    if ($event) {
      return [
        'id' => $event->id,
        'name' => $event->name,
        'status' => $event->status,
        'start_date' => $event->start_date,
        'end_date' => $event->end_date,
        'image' => $event->image,
        'description' => $event->description,
      ];
    }
  }
}
