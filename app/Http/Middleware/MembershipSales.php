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

class MembershipSales {
    use MembershipAccess;

    public function handle(Request $request, Closure $next): Response {
        $language = $request->query('language', 'en');
        $eventId =
            $request->route('eventId') ??
            ($request->route('event_id') ??
                ($request->get('eventId') ??
                    ($request->get('event_id') ??
                        ($request->query('eventId') ?? ($request->query('event_id') ?? null)))));

        App::setLocale($language);

        // check if the Auth
        $user = Auth::check() ? Auth::user() : null;
        $customer = $user && $user->hasRole('Customer') ? $user : null;

        //Log::info("Customer: " . json_encode($customer));

        $membership = null;

        if ($customer) {
            $membership = $customer->membershipTypes->first() ?? null;
        }

        $access = $this->checkMembershipAccess($membership, null, $eventId);

        if ($access['status'] === false) {
            return response()->json(
                [
                    'purchase_access' => $access['status'],
                    'access_message' => $access['message'] ?? null,
                ],
                403,
            );
        }

        // Attach access information to the request
        $request->merge([
            'purchase_access' => $access['status'],
            'access_message' => $access['message'] ?? null,
        ]);

        return $next($request);
    }
}
