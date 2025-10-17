<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Helpers\CustomerHelper;
use App\Http\Requests\CustomerRequest;
use App\Interfaces\CustomerInterface;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\SurvivorNumber;
use App\Models\Tag;
use App\Models\Passenger;
use App\Models\User;
use App\Models\UserTag;
use App\Models\TemporaryPassword;
use App\Repositories\CustomerRepository;
use App\Services\CustomerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Http\RedirectResponse;
use Inertia\Response as InertiaResponse;
use DB;
use Illuminate\Http\Response;
use Log;

class CustomerController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;

  protected CustomerInterface $customerRepository;
  protected CustomerService $customerService;

  public function __construct(CustomerRepository $customerRepository, CustomerService $customerService)
  {
    $this->customerRepository = $customerRepository;
    $this->customerService = $customerService;
  }

  public function index(Request $request)
  {
    try {
      return $this->withPermission([Permissions::ViewCustomers], function () {
        $userTags = Tag::type('customer')->get();
        return Inertia::render('Customer/Index', [
          'customers' => $this->customerRepository->getAllCustomerData(),
          'userTags' => $userTags,
        ]);
      }, $request);
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }
  }

  public function getPaginated(Request $request): LengthAwarePaginator
  {
    $page = $request->get('page');
    $perPage = $request->get('per_page');
    $sortBy = $request->get('sort_by');
    $sortDir = $request->get('sort_direction');
    $filters = json_decode($request->get('filters'), true);
    $tags = json_decode($request->get('tags'), true);

    return $this->customerRepository->getPaginatedCustomerData($page, $perPage, $sortBy, $sortDir, $filters, $tags);
  }

  public function create(): InertiaResponse
  {
    return Inertia::render('Customer/Create');
  }

  public function store(CustomerRequest $request): RedirectResponse|Response|InertiaResponse
  {
    try {
      return $this->withPermission([Permissions::CreateCustomers], function ($request) {
        $this->customerRepository->store($request);

        return redirect()->route('customers.index')->with('flash', 'Customer created successfully.');
      }, $request);
    } catch (\Exception $e) {
      $this->logException($e);
      return redirect()->route('customers.index')->with('error', 'Problem creating customer.');
    }
  }

  public function edit(User $user): InertiaResponse
  {
    $user->load(['detail', 'survivorNumber', 'customerAddress']);
    $bookings = $this->customerRepository->getBookingDataForCustomer($user);

    return Inertia::render('Customer/Edit', [
      'customer' => $user,
      'bookings' => $bookings,
    ]);
  }

  public function update(CustomerRequest $request, User $user)
  {
    try {
      $this->customerService->updateCustomer($request, $user);

      return redirect()->route('customers.edit', $user->id)
        ->with('success', 'Customer updated successfully.');
    } catch (\Exception | \Throwable $e) {
      return redirect()->route('customers.edit', $user->id)->with('error', 'Problem updating customer.');
    }
  }

  public function show(User $user): RedirectResponse|Response|InertiaResponse
  {

    try {
      return $this->withPermission([Permissions::ViewCustomers], function ($user) {
        //$user->load(['detail', 'survivorNumber', 'customerAddress', 'bookings', 'comments.author', 'logs.author');
        // @todo check why tags are not loaded
        $user = Customer::with([ 'tags','detail', 'survivorNumber', 'customerAddress', 'bookings', 'comments.author', 'logs.author'])->find($user->id);
        $bookings = $this->customerRepository->getBookingDataForCustomer($user);
        $availableTags = Tag::type('customer')->get();
        // check if user have generated temporary password
        $isTemporaryPassword = TemporaryPassword::where('customer_id', $user->id)
          ->exists();
        return Inertia::render('Customer/View', [
          'customer' => $user,
          'bookings' => $bookings,
          'availableTags' => $availableTags,
          'isTemporaryPassword' => $isTemporaryPassword,
        ]);
      }, $user);
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }
  }

  public function destroy(User $user): RedirectResponse|Response|InertiaResponse
  {
    try {
      return $this->withPermission([Permissions::DeleteCustomers], function ($user) {
        $this->customerRepository->delete($user);

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
      }, $user);
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }
  }

  public function editBySurvivorNumber(string $survivorNumber)
  {
    if (!$survivorNumber) {
      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }

    $survivorNumberModel = SurvivorNumber::query()
      ->with(['user'])
      ->where('survivor_number', $survivorNumber)
      ->first();

    if (!$survivorNumberModel) {
      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }

    $customer = $survivorNumberModel->user;

    if (!$customer) {
      return redirect()->route('customers.index')->with('error', 'Something went wrong.');
    }

    return $this->edit($customer);
  }

  public function addComment(Request $request): Response|RedirectResponse
  {
    try {
      $customerId = request()->route('user');
      $comment = $request->input('comment');

      return $this->withPermission(
        [Permissions::EditCustomers],
        function ($customerId, $comment) {
          $customer = $this->customerRepository->find($customerId);
          $result = $this->customerRepository->addComment($customer, $comment);

          if ($result) {
            return redirect()
              ->route('customers.show', ['customer' => $customerId])
              ->with('success', 'Comment added successfully.');
          }

          return redirect()
            ->route('customers.show', ['customer' => $customerId])
            ->with('error', 'Failed to add comment.');
        },
        $customerId,
        $comment
      );
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()
        ->route('customers.show', ['customer' => $customerId])
        ->with('error', 'Failed to add comment.');
    }
  }

  public function updateTags(Request $request)
  {
    try {
      $customerId = request()->route('user');
      $tags = $request->input('tags');
      return $this->withPermission(
        [Permissions::EditCustomers],
        function ($customerId, $tags) {
          $customer = Customer::find($customerId);
          $result = $this->customerRepository->addTags($customer, $tags);
          if ($result) {
            return redirect()
              ->route('customers.show', ['customer' => $customerId])
              ->with('success', 'Tags updated successfully.');
          }

          return redirect()
            ->route('customers.show', ['customer' => $customerId])
            ->with('error', 'Failed to update tags.');
        },
        $customerId,
        $tags
      );
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()
        ->route('customers.show', ['customer' => $customerId])
        ->with('error', 'Failed to update tags.');
    }
  }

  public function deleteComment(Request $request)
  {
    try {
      $customerId = $request->route('user');
      $commentId = $request->get('comment_id');
      return $this->withPermission(
        [Permissions::EditCustomers],
        function ($customerId, $commentId) {
          $customer = User::find($customerId);
          $result = $this->customerRepository->deleteComment($customer, $commentId);
          if ($result) {
            return redirect()
              ->route('customers.show', ['customer' => $customerId])
              ->with('success', 'Comment deleted successfully.');
          }

          return redirect()
            ->route('customers.show', ['customer' => $customerId])
            ->with('error', 'Failed to delete comment.');
        },
        $customerId,
        $commentId
      );
    } catch (\Exception $e) {
      $this->logException($e);

      return redirect()
        ->route('customers.show', ['customer' => $customerId])
        ->with('error', 'Failed to delete comment.');
    }
  }

  /**
   * Search users by first_name, last_name, or email.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function search(Request $request)
  {
    $request->validate([
      'query' => 'required|string|min:3',
      'eventId' => 'nullable|integer|min:1',
      'bookingId' => 'nullable|integer|min:1',
    ]);

    $searchQuery = $request->get('query');

    try {
      $booking = Booking::with('passengers')->findOrFail($request->bookingId);
      $currentLead = $booking->passengers->firstWhere('lead_passenger', true);
      $currentSurvivor = SurvivorNumber::where('survivor_number', $currentLead->survivor_number)->first();
      $currentLeadUser = $currentSurvivor
        ? User::with('membershipTypes')->find($currentSurvivor->user_id)
        : null;

      $currentMaxMembership = $currentLeadUser?->membershipTypes
        ->sortByDesc('booking_number_requirement')
        ->first()?->booking_number_requirement;
      $sameBookingBySN = $booking->passengers->keyBy('survivor_number');
      $users = User::with(['detail', 'survivorNumber', 'customerAddress', 'membershipTypes'])
        ->where(function ($query) use ($searchQuery) {
          $query->where('email', 'LIKE', "%{$searchQuery}%")
            ->orWhereHas('detail', function ($q) use ($searchQuery) {
               $q->where('first_name', 'ilike', "%{$searchQuery}%")
                  ->orWhere('last_name', 'ilike', "%{$searchQuery}%");
            });
        })
        ->get();

      $results = $users->map(function ($user) use ($request, $currentMaxMembership, $booking, $sameBookingBySN) {

        $survivorNumber = $user->survivorNumber?->survivor_number;
        $passengerInSame = $survivorNumber ? $sameBookingBySN->get($survivorNumber) : null;
        $inSameBooking   = (bool) $passengerInSame;
        $isCurrentLead   = (bool) ($passengerInSame?->lead_passenger);
        $hasBooking = Passenger::checkSurvivorInActiveBookings($survivorNumber, $request->eventId, excludeBookingId: $booking->id);
        if ($isCurrentLead) {
          $hasBooking = true;
        }

        $userMax = $user->membershipTypes
          ->sortByDesc('booking_number_requirement')
          ->first()?->booking_number_requirement ?? null;

        $isLowerTier = $userMax && $currentMaxMembership
          ? $userMax < $currentMaxMembership
          : false;

        $detail  = $user->detail;
        $address = $user->customerAddress;

        return [
          'id' => $user->id,
          'email' => $user->email,
          'first_name' => $detail->first_name ?? null,
          'last_name' => $detail->last_name ?? null,
          'middle_name' => $detail->middle_name ?? null,
          'survivor_number' => $survivorNumber ?? null,
          'confirmed_booking_email' => $detail->confirmed_booking_email ?? null,
          'lead_passenger' => $detail->lead_passenger ?? null,
          'payment_method' => $detail->payment_method ?? null,
          'phone' => $detail->phone ?? null,
          'address_first' => $address->address_first ?? null,
          'address_second' => $address->address_second ?? null,
          'city' => $address->city ?? null,
          'state' => $address->state ?? null,
          'postal_code' => $address->postal_code ?? null,
          'country' => $address->country ?? null,
          'citizenship' => $detail->citizenship ?? null,
          'gender' => $detail->gender ?? null,
          'dob' => $detail->dob ?? null,
          'full_name' => trim(($detail->first_name ?? '') . ' ' . ($detail->last_name ?? '')),
          'emergency_c_name' => $detail->emergency_c_name ?? null,
          'emergency_c_phone' => $detail->emergency_c_phone ?? null,
          'special_request' => $detail->special_request ?? null,
          'hear_about' => $detail->hear_about ?? null,
          'newsletter' => $detail->newsletter ?? null,
          'travel_info' => $detail->travel_info ?? null,
          'terms_n_cons' => $detail->terms_n_cons ?? ($detail->term_n_cons ?? null),
          'cabin_conf_accp' => $detail->cabin_conf_accp ?? null,
          'single_t_agreement' => $detail->single_t_agreement ?? null,
          'passenger_allocated_cost' => $detail->passenger_allocated_cost ?? null,
          'passenger_balance' => $detail->passenger_balance ?? null,
          'was_on_board' => $detail->was_on_board ?? null,
          'has_booking' => $hasBooking,    
          'lower_tier'  => $isLowerTier,
          'in_same_booking' => $inSameBooking,
          'is_current_lead' => $isCurrentLead,
        ];
      });

      return response()->json($results);
    } catch (\Exception $e) {
      Log::error('Search error: ' . $e->getMessage());
      return response()->json(['error' => 'Internal error'], 500);
    }
  }
}
