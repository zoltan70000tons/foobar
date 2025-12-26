<?php

namespace App\Http\Controllers;

use App\Enums\SystemAdjustment;
use App\Enums\Gender;
use App\Enums\CabinType;
use App\Enums\Permissions;
use App\Helpers\PriceCalculation;
use App\Interfaces\BookingInterface;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Interfaces\LogInterface;
use App\Interfaces\TeamRepositoryInterface;
use App\Models\Adjustment;
use App\Models\Booking;
use App\Models\BookingAgentSessions;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use App\Models\CabinSpec;
use App\Models\Event;
use App\Models\Tag;
use App\Models\Passenger;
use App\Models\SurvivorNumber;
use App\Models\User;
use App\Models\Payment;
use App\Repositories\AdjustmentsRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use App\Repositories\LogRepository;
use App\Repositories\TagRepository;
use App\Repositories\TeamRepository;
use App\Rules\UniqueSurvivorInEvent;
use App\Traits\CabinFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Services\PaymentInfoService;
use App\Helpers\InstallmentHelper;
use App\Http\Requests\UpdateBedConfigRequest;

class BookingsController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;
  use CabinFilter;

  protected EventRepositoryInterface $eventRepository;
  protected BookingInterface $bookingRepository;
  protected LogInterface $logRepository;
  protected TeamRepositoryInterface $teamRepository;
  protected CabinInterface $cabinRepository;
  protected CabinCategoryInterface $cabinCategoryRepository;
  protected AdjustmentsRepository $adjustmentsRepository;
  protected PaymentInfoService $paymentInfoService;

  protected TagRepository $tagRepository;

  public function __construct(
    EventRepository $eventRepository,
    BookingRepository $bookingRepository,
    LogRepository $logRepository,
    TeamRepository $teamRepository,
    CabinRepository $cabinRepository,
    CabinCategoryRepository $cabinCategoryRepository,
    AdjustmentsRepository $adjustmentsRepository,
    PaymentInfoService $paymentInfoService,
    TagRepository $tagRepository
  ) {
    $this->eventRepository = $eventRepository;
    $this->bookingRepository = $bookingRepository;
    $this->logRepository = $logRepository;
    $this->teamRepository = $teamRepository;
    $this->cabinRepository = $cabinRepository;
    $this->cabinCategoryRepository = $cabinCategoryRepository;
    $this->adjustmentsRepository = $adjustmentsRepository;
    $this->paymentInfoService = $paymentInfoService;
    $this->tagRepository = $tagRepository;
  }
  public function index(Request $request)
  {
    // set default to ON HOLD
    $tab = $request->get('tab', 1);

    $status = match ((int) $tab) {
      0 => 'NEW',
      1 => 'ON HOLD',
      2 => 'UPLOADED',
      3 => 'CANCELLED',
      default => 'ON HOLD',
    };

    try {
      $event_id = request()->route('id');
      $keyword = $request->input('keyword');
      $tag = $request->input('tag');
      if ($event_id == 'all') {
        $events = $this->eventRepository->getAll();
        return Inertia::render('Bookings/partials/Events', [
          'events' => $events,
        ]);
      }
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($event_id, $keyword, $tag) use ($status, $tab) {
          $event = $this->eventRepository->find($event_id);
          $users = $this->teamRepository->getAllMembers(1);
          $cabinTypes = $this->cabinRepository->getTypes();
          $tags = $this->tagRepository->getAll();

          return Inertia::render('Bookings/Index', [
            'event' => $event,
            'bookings' => [],
            'users' => $users,
            'cabinTypes' => $cabinTypes,
            // Cabin categories are fetched on demand in the modal to avoid loading cost here
            'cabinCategories' => [],
            'tabIndex' => (int) $tab,
            'keyword' => $keyword,
            'tags' => $tags,
          ]);
        },
        $event_id,
        $keyword,
        $tag
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  /*
  |--------------------------------------------------------------------------
  | Get Cabin Categories for Booking Flow
  |--------------------------------------------------------------------------
  |
  |  Fetch the cabin categories for the booking flow modal
  |
  | @param Request $request
  | @param int $eventId
  | @return \Illuminate\Http\JsonResponse
  |
  */
  public function getCabinCategories(Request $request, $eventId)
  {
    try {
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($eventId) {
          $categories = $this->cabinCategoryRepository->getCategoriesWithCabins($eventId);
          return response()->json(['cabinCategories' => $categories]);
        },
        $eventId
      );
    } catch (Exception $e) {
      $this->logException($e);
      return response()->json(['error' => 'Unable to load cabin categories'], 500);
    }
  }

  public function create()
  {
  }

  public function store(Request $request)
  {
    $event_id = request()->route('id');

    // Pre load cabin for validation rules
    $cachedCabin = null;
    $getCabin = function () use ($request, &$cachedCabin) {
      if ($cachedCabin === null) {
        $cabin_number = $request->input('cabin_number');
        $cabinCategoryId = $request->input('cabin_category_id');

        $cachedCabin = Cabin::with(['cabinType', 'category.spec'])
          ->whereHas('cabinSpec', function ($query) use ($cabin_number, $cabinCategoryId) {
            $query->where('cabin_number', $cabin_number)->where('cabin_category_id', $cabinCategoryId);
          })
          ->first();
      }
      return $cachedCabin;
    };

    $validated = $request->validate([
      'cabin_number' => ['required', 'string', 'exists:cabin_specs,cabin_number'],
      'cabin_category_id' => ['required', 'integer', 'exists:cabin_categories,id'],
      'payment_plan' => ['required', Rule::in(['INSTALLMENTS', 'PAY_IN_FULL'])],
      'bed_configuration' => ['required', Rule::in('SEPARATED', 'JOINED')],
      'carbon_offset' => ['required', 'boolean'],
      'you_choose_your_cabin' => ['required', 'boolean'],
      'number_of_installments' => ['nullable', 'integer', 'min:1', 'required_if:payment_plan,INSTALLMENTS'],
      'passenger.id' => ['required', 'string', 'exists:users,id'],
      'passenger.first_name' => ['required', 'string', 'max:255'],
      'passenger.middle_name' => ['nullable', 'string', 'max:255'],
      'passenger.last_name' => ['required', 'string', 'max:255'],
      'passenger.dob' => ['required', 'date', 'before:today'],
      'passenger.gender' => [
        'required',
        Rule::in(Gender::values()),
        function ($attribute, $value, $fail) use ($getCabin) {
          $cabin = $getCabin();

          if (!$cabin) {
            return $fail('Invalid cabin selection.');
          }

          if ($cabin->cabin_type_id === CabinType::SINGLE_MALE->value && $value === Gender::FEMALE->value) {
            return $fail('Female passengers cannot be assigned to a Single Male cabin.');
          }

          if ($cabin->cabin_type_id === CabinType::SINGLE_FEMALE->value && $value === Gender::MALE->value) {
            return $fail('Male passengers cannot be assigned to a Single Female cabin.');
          }
        },
      ],
      'passenger.citizenship' => ['nullable', 'string', 'max:3'],
      'passenger.survivor_number' => [
        'required',
        'string',
        'regex:/^\d+$/',
        'exists:survivor_numbers,survivor_number',
        new UniqueSurvivorInEvent($event_id),
      ],
      'passenger.email' => ['required', 'email'],
      'passenger.phone' => ['nullable', 'string', 'max:20'],
      'passenger.address_first' => ['required', 'string', 'max:255'],
      'passenger.address_second' => ['nullable', 'string', 'max:255'],
      'passenger.city' => ['required', 'string', 'max:255'],
      'passenger.state' => ['nullable', 'string', 'max:255'],
      'passenger.postal_code' => ['nullable', 'string', 'max:20'],
      'passenger.country' => ['required', 'string', 'max:3'],
      'passenger.emergency_c_name' => ['nullable', 'string', 'max:255'],
      'passenger.emergency_c_phone' => ['nullable', 'string', 'max:20'],
      'passenger.payment_method' => ['required', Rule::in(['CREDIT_CARD', 'BANK_TRANSFER'])],
      'passenger.special_request' => ['nullable', 'string', 'max:1000'],
      'passenger.special_options' => ['nullable', 'array'],
      'passenger.dietary_preferences' => ['nullable', 'array'],
      'passenger.lead_passenger' => ['required', 'boolean'],
      'passenger.travel_info' => ['required', 'boolean'],
      'passenger.terms_n_cons' => ['required', 'accepted'],
      'passenger.single_t_agreement' => [
        'required',
        'boolean',
        function ($attribute, $value, $fail) use ($getCabin) {
          $cabin = $getCabin();

          if (
            $cabin &&
            in_array($cabin->cabin_type_id, [CabinType::SINGLE_MALE->value, CabinType::SINGLE_FEMALE->value])
          ) {
            if (!$value) {
              $fail('The STA must be checked when the cabin type Single');
            }
          }
        },
      ],
    ]);

    try {
      $user = $request->user();
      $passenger_data = $validated['passenger'];
      $passenger_data['cabin_conf_accp'] = true; //CCA is a required field, thus should go set as true by default - Nic
      $number_of_installments = $validated['number_of_installments'] ?? 1;
      $payment_plan = $validated['payment_plan'];
      $carbonOffset = $validated['carbon_offset'];
      $youChooseYourCabin = $validated['you_choose_your_cabin'];
      $bedConfig = $validated['bed_configuration'];
      $cabin = $getCabin();

      return $this->withPermission(
        [Permissions::CreateBookings],
        function (
          $event_id,
          $cabin,
          $user,
          $passenger_data,
          $payment_plan,
          $number_of_installments,
          $carbonOffset,
          $youChooseYourCabin,
          $bedConfig
        ) {
          if ($cabin) {
            $adjustmentIds = [];

            // Build list of adjustment codes needed
            $adjustmentCodes = [SystemAdjustment::TAX->value]; // TAX is always applied for manual bookings

            // Carbon Offset
            if ($carbonOffset === true) {
              $carbonOffsetCode = SystemAdjustment::carbonOffset(
                $cabin->category->spec->getFirstLetterOfCategoryType()
              );
              $adjustmentCodes[] = $carbonOffsetCode->value;
            }

            // You Choose Your Cabin
            if ($youChooseYourCabin === true) {
              $adjustmentCodes[] = SystemAdjustment::CHOOSE_YOUR_CABIN->value;
            }

            // Payment in Full
            if ($payment_plan === 'PAY_IN_FULL') {
              $adjustmentCodes[] = SystemAdjustment::PAID_IN_FULL->value;
            }

            // Single Ticket Fee for Single Cabins
            if (
              $cabin->cabinType->id === CabinType::SINGLE_MALE->value ||
              $cabin->cabinType->id === CabinType::SINGLE_FEMALE->value
            ) {
              $adjustmentCodes[] = SystemAdjustment::SINGLE_TICKET_FEE->value;
            }

            // Fetch all adjustments in a single query
            $adjustments = $this->adjustmentsRepository->getAdjustmentsByCodes($adjustmentCodes, $event_id);

            // Membership Level Adjustment based on Survivor Number
            if (
              isset($passenger_data['survivor_number']) &&
              isset($passenger_data['lead_passenger']) &&
              $passenger_data['lead_passenger'] === true
            ) {
              $membershipAdjustment = $this->adjustmentsRepository->getAdjustmentsBySurvivorNumber(
                $passenger_data['survivor_number'],
                $event_id
              );

              if ($membershipAdjustment) {
                $adjustments->push($membershipAdjustment);
              }
            }

            // Collect adjustment IDs
            $adjustmentIds = $adjustments->pluck('id')->toArray();

            $bookingData = [
              'event_id' => $event_id,
              'customer_id' => $passenger_data['id'],
              'payment_plan' => $payment_plan,
              'number_of_installments' => $number_of_installments,
              'bed_config' => $bedConfig,
              'addons' => array_map(fn($id) => ['id' => $id], $adjustmentIds),
            ];

            $this->bookingRepository->createBooking($bookingData, $passenger_data, $cabin);
          }
        },
        $event_id,
        $cabin,
        $user,
        $passenger_data,
        $payment_plan,
        $number_of_installments,
        $carbonOffset,
        $youChooseYourCabin,
        $bedConfig
      );
      return redirect()
        ->back()
        ->with('flash', [
          'message' => 'Booking created successfully.',
          'success' => true,
        ]);
    } catch (Exception $e) {
      $this->logException($e);
      return redirect()
        ->back()
        ->with('flash', [
          'message' => 'Error creating booking.',
          'success' => false,
        ]);
    }
  }

  public function edit(Request $request)
  {
  }

  public function assignAgent(Request $request)
  {
    try {
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($request) {
          $request->validate([
            'agent_id' => 'nullable|exists:users,id',
            'booking_code' => 'required|exists:bookings,booking_code',
          ]);
          $agent_id = $request->input('agent_id');
          $booking_code = $request->input('booking_code');
          $booking = $this->bookingRepository->findByCode($booking_code);
          if ($booking) {
            if (is_null($agent_id)) {
              $booking->agent_id = null;
              $booking->save();
              return redirect()->back()->with('message', 'Agent unassigned successfully.');
            } else {
              $booking->agent_id = $agent_id;
              $booking->save();
              return redirect()->back()->with('message', 'User assigned successfully.');
            }
          }
        },
        $request
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function update(Request $request)
  {
    try {
      $event_id = request()->route('id');
      $booking_code = request()->route('booking_code');
      $tags = $request->input('selectedTags');
      $user = $request->user();
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_code, $tags, $user) {
          $event = $this->eventRepository->find($event_id);
          $booking = $this->bookingRepository->findByCode($booking_code);
          $this->bookingRepository->update(['tags' => $tags], $booking->id);
          return Inertia::render('Bookings/partials/Show', [
            'event' => $event,
            'booking' => $booking,
          ]);
        },
        $event_id,
        $booking_code,
        $tags,
        $user
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function show(Request $request)
  {
    try {
      $event_id = request()->route('id');
      $booking_code = request()->route('booking_code');
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($event_id, $booking_code) {
          $event = $this->eventRepository->find($event_id);
          $booking = $this->bookingRepository->findByCode($booking_code);
          $isEditable = $booking->agent_id === Auth::user()->id;
          $users = $this->teamRepository->getAllMembers(1);
          $cabinTypes = $this->cabinRepository->getTypes();
          $adjustments = $this->adjustmentsRepository->listAdjustments($event_id);
          $cabinCategories = $this->cabinCategoryRepository->getCategoriesByEvent($event_id);
          $availableTags = Tag::type('booking')->get();
          $bookingId = $booking->id;
          $history = $this->logRepository->getHistory($bookingId);
          $latestBipId = Payment::onlyTrashed()
            ->where('splitAmount', true)
            ->whereHas('passenger', function ($query) use ($bookingId) {
              $query->where('booking_id', $bookingId);
            })
            ->orderBy('deleted_at', 'desc')
            ->value('BIP_ID');
          $deletedPayments = Payment::onlyTrashed()
            ->where('splitAmount', true)
            ->where('BIP_ID', $latestBipId)
            ->whereHas('passenger', function ($query) use ($bookingId) {
              $query->where('booking_id', $bookingId);
            })
            ->get();

          return Inertia::render('Bookings/partials/Show', [
            'event' => $event,
            'booking' => $booking,
            'maxInstallmentsAllowed' => InstallmentHelper::maxInstallmentsAllowed($booking),
            'users' => $users,
            'isEditable' => $isEditable,
            'cabinTypes' => $cabinTypes,
            'cabinCategories' => $cabinCategories,
            'adjustments' => $adjustments,
            'availableTags' => $availableTags,
            'deletedPayments' => $deletedPayments,
            'history' => $history,
          ]);
        },
        $event_id,
        $booking_code
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function destroy(Cabin $cabin)
  {
  }

  public function addTag(Request $request)
  {
  }

  // Reassign booking to another agent
  public function reAssign(Request $request)
  {
    $bookingId = $request->input('booking_id');
    $user = Auth::user();

    // is user have permissio EditBookings
    if (!$user->hasAnyPermission([Permissions::InterceptBookings])) {
      // return back with error message
      return redirect()->back()->with('error', 'You do not have permission to reassign this booking.');
    }

    BookingAgentSessions::where('booking_id', $bookingId)->delete();

    BookingAgentSessions::create([
      'booking_id' => $bookingId,
      'agent_id' => $user->id,
      'time' => now(),
    ]);

    broadcast(
      new \App\Events\BookingAgentSession(agentId: $user->id, bookingId: $bookingId, username: $user->username)
    );

    return Inertia::location(url()->previous());
  }

  // edit mode
  public function editMode(Request $request)
  {
    try {
      $bookingId = $request->input('booking_id');
      $eventId = $request->input('event_id');
      $lock = $request->input('lock') === '1';
      $user = Auth::user();

      if ($lock) {
        BookingAgentSessions::updateOrCreate(
          [
            'booking_id' => $bookingId,
            'agent_id' => $user->id,
          ],
          [
            'time' => now(),
          ]
        );
      } else {
        BookingAgentSessions::where('booking_id', $bookingId)
          ->where('agent_id', $user->id)
          ->delete();
      }

      broadcast(
        new \App\Events\BookingAgentSession(
          agentId: $user->id,
          bookingId: $bookingId,
          username: $lock ? $user->username : null
        )
      );
    } catch (Exception $e) {
      $this->logException($e);
    }

    // // return inertia
    return $this->withPermission([Permissions::EditBookings], fn() => Inertia::location(url()->previous()));
  }

  public function statusUpdate(Request $request)
  {
    try {
      $event_id = request()->route('id');

      $booking_id = $request->input('booking_id');
      $status = $request->input('status');

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $status) {
          //$event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->changeStatus($booking, $status);
          if ($result) {
            return redirect()
              ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
              ->with('success', 'Status updated successfully.');
          }
        },
        $event_id,
        $booking_id,
        $status
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function cabinUpdate(Request $request)
  {
    try {
      $event_id = request()->route('id');

      $booking_id = $request->input('booking_id');
      $cabinNumber = $request->input('cabin_number');
      $cabinCategoryId = $request->input('cabin_category_id');
      $typeId = $request->input('cabin_type_id');
      $currentCapacity = $request->input('capacity');

      $selectedCabin = $this->bookingRepository->getBookableCabinByParams(
        $currentCapacity,
        $cabinNumber,
        $typeId,
        $cabinCategoryId
      );

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $cabin) {
          $booking = Booking::find($booking_id);

          $result = $this->bookingRepository->changeCabin($booking, $cabin);

          return redirect()
            ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
            ->with('success', 'Cabin updated successfully.');
        },
        $event_id,
        $booking_id,
        $selectedCabin
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function codeUpdate(Request $request)
  {
    try {
      $event_id = request()->route('id');
      $booking_id = $request->input('booking_id');
      $booking_code = $request->input('booking_code');

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $booking_code) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->changeCode($booking, $booking_code);
          if ($result) {
            return redirect()
              ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
              ->with('success', 'Booking code updated successfully.');
          }
        },
        $event_id,
        $booking_id,
        $booking_code
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function addComment(Request $request)
  {
    try {
      $event_id = request()->route('id');
      $booking_id = $request->input('booking_id');
      $comment = $request->input('comment');
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $comment) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->addComment($booking, $comment);
          if ($result) {
            return redirect()
              ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
              ->with('success', 'Comment added successfully.');
          }
        },
        $event_id,
        $booking_id,
        $comment
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function updateTags(Request $request)
  {
    try {
      $event_id = request()->route('id');
      $booking_id = $request->input('booking_id');
      $tags = $request->input('tags');
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $tags) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->addTags($booking, $tags);
          if ($result) {
            return redirect()
              ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
              ->with('success', 'Tags updated successfully.');
          }
        },
        $event_id,
        $booking_id,
        $tags
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function getAvailableCabins(Request $request)
  {
    try {
      $categoryId = $request->get('category_id');
      $typeId = $request->get('type_id');
      $deck = $request->get('deck');
      $location = $request->get('location');
      $accessible = $request->boolean('accessible');

      $cabinsData = $this->filterCabins($typeId, $categoryId, null, true, null, null, false, true);

      if (isset($cabinsData['error'])) {
        return response()->json(
          [
            'error' => $cabinsData['error'],
          ],
          $cabinsData['status'] ?? 404
        );
      }

      $filteredCabins = collect($cabinsData['cabins'])
        ->when($deck, function ($collection, $deck) {
          return $collection->where('deck', $deck);
        })
        ->when($location, function ($collection, $location) {
          return $collection->where('location', $location);
        })
        ->when($accessible, function ($collection) {
          return $collection->where('accessible', true);
        });

      return response()->json([
        'cabins' => $filteredCabins->sortBy('cabin_number')->values()->all(),
      ]);
    } catch (Exception $e) {
      //throw $th;
    }
  }

  public function cancel(Request $request)
  {
    $request->validate([
      'booking_id' => 'required|exists:bookings,id',
    ]);

    $event_id = request()->route('id');
    $result = null;

    try {
      $booking = Booking::findOrFail($request->booking_id);
      if ($booking->status === 'CANCELLED') {
        throw new Exception('This booking is already cancelled.');
      }

      $result = $this->bookingRepository->cancel($booking);

      if ($result) {
        session()->flash('success', 'Booking was cancelled successfully.');
      } else {
        session()->flash('error', 'Error cancelling booking.');
      }
    } catch (Exception $e) {
      session()->flash('error', $e->getMessage());
    } finally {
      return Inertia::location(
        route('bookings.show', [
          'id' => $event_id,
          'booking_code' => $result?->booking_code ?? ($booking->booking_code ?? ''),
        ])
      );
    }
  }

  public function getCabinsToUpgradeTo(Request $request)
  {
    try {
      $categoryId = $request->get('category_id');
      $typeId = $request->get('type_id');
      $cabinNumber = $request->get('cabin_number');
      $location = $request->get('location');
      $accessible = $request->boolean('accessible');

      $currentCabin = Cabin::with(['category.spec', 'cabinSpec', 'cabinType'])
        ->where('cabin_category_id', $categoryId)
        ->whereHas('cabinType', function ($q) use ($typeId) {
          $q->where('id', $typeId);
        })
        ->whereHas('cabinSpec', function ($q) use ($cabinNumber) {
          $q->where('cabin_number', $cabinNumber);
        })
        ->firstOrFail();

      $currentPrice = $currentCabin->category->price;
      $currentCapacity = $currentCabin->category->spec->capacity;
      $currentCabinNumber = $currentCabin->cabinSpec->cabin_number;

      $upgradeCabins = Cabin::with(['category.spec', 'cabinSpec', 'cabinType'])
        ->whereHas('category.spec', function ($q) use ($currentCapacity) {
          $q->where('capacity', $currentCapacity);
        })
        ->whereHas('cabinSpec', function ($q) use ($currentCabinNumber) {
          $q->where('cabin_number', '!=', $currentCabinNumber);
        })
        ->whereHas('category', function ($q) use ($currentPrice) {
          $q->where('price', '>', $currentPrice);
        })
        ->whereIn('status', ['AVAILABLE', 'PARTIALLY_BOOKED', 'RESERVED'])
        ->whereDoesntHave('temporaryReservations', function ($q) {
          $q->where('expires_at', '>', now());
        })
        ->where('cabin_type_id', $typeId)
        ->get()
        ->sortBy('cabinSpec.cabin_number', SORT_ASC);

      if ($upgradeCabins->count() < 1) {
        return response()->json(
          [
            'error' => 'No cabin found',
          ],
          404
        );
      }

      return response()->json([
        'cabins' => $upgradeCabins->values(),
      ]);
    } catch (Exception $e) {
      //throw $th;
    }
  }

  public function getData(Request $request)
  {
    try {
      $eventId = $request->route('id');
      $status = match ((int) $request->get('tab', 1)) {
        0 => 'NEW',
        1 => 'ON HOLD',
        2 => 'UPLOADED',
        3 => 'CANCELLED',
        default => 'ON HOLD',
      };

      $keyword = $request->input('keyword');
      $perPage = $request->input('per_page', 10);
      $sortKey = $request->input('sort_key', 'created_at');
      $sortDirection = $request->input('sort_direction', 'desc');
      $tags = array_filter(explode(',', $request->input('tags', '')));
      $user_ids = $request->input('user_ids', []);
      $dateRange = $request->input('date_range', null);
      $bookings = $this->bookingRepository->getByStatus(
        $eventId,
        $status,
        $keyword,
        $perPage,
        $sortKey,
        $sortDirection,
        $tags,
        $user_ids,
        $dateRange
      );

      return response()->json([
        'data' => $bookings->items(),
        'total' => $bookings->total(),
      ]);
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function switchLeadPassenger(Request $request)
  {
    $newLeadId = $request->input('new_lead_passenger_id');
    $eventId = $request->route('id');
    $bookingId = $request->route('booking_id');

    try {
      return $this->withPermission([Permissions::EditBookings], function () use (
        $bookingId,
        $newLeadId,
        $eventId,
        $request
      ) {
        return DB::transaction(function () use ($bookingId, $newLeadId, $request, $eventId) {
          $booking = Booking::with('passengers')->findOrFail($bookingId);
          $currentLead = $booking->passengers->firstWhere('lead_passenger', true);
          $currentSurvivor = SurvivorNumber::where('survivor_number', $currentLead->survivor_number)->first();
          $currentLeadUser = $currentSurvivor ? User::with('membershipTypes')->find($currentSurvivor->user_id) : null;
          $user = User::with('survivorNumber')->findOrFail($newLeadId);
          $sn = $user->survivorNumber?->survivor_number;
          if (!$sn) {
            return response()->json(['error' => 'Selected user has no Survivor Number.'], 422);
          }

          $hasBooking = Passenger::checkSurvivorInActiveBookings($sn, $booking->event_id, $bookingId);
          if ($hasBooking) {
            return response()->json(
              ['error' => 'Selected user is already in another active booking for this event.'],
              422
            );
          }

          if (!$user->hasRole('Customer')) {
            return response()->json(['error' => 'The selected user is not a valid lead passenger.'], 422);
          }
          $currentMaxMembership = $currentLeadUser?->membershipTypes->sortByDesc('booking_number_requirement')->first()
            ?->booking_number_requirement;
          $userMax =
            $user->membershipTypes->sortByDesc('booking_number_requirement')->first()?->booking_number_requirement ??
            null;
          $isLowerTier = $userMax && $currentMaxMembership ? $userMax < $currentMaxMembership : false;
          if ($isLowerTier) {
            return response()->json(
              ['error' => 'The selected user has a lower membership tier than the current lead passenger.'],
              422
            );
          }

          $leadPassengerSlot = Passenger::where('booking_id', $bookingId)->where('lead_passenger', true)->firstOrFail();

          $oldSurvivorNumber = $leadPassengerSlot->survivor_number;

          $passengerData = $request->except(['new_lead_passenger_id']);
          foreach (
            [
              'confirmed_booking_email',
              'newsletter',
              'travel_info',
              'terms_n_cons',
              'cabin_conf_accp',
              'single_t_agreement',
              'was_on_board',
            ]
            as $field
          ) {
            $passengerData[$field] = $request->has($field)
              ? filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN)
              : false;
          }
          $passengerData['terms_n_cons'] = true;
          $passengerData['cabin_conf_accp'] = true;

          // new in the same booking?
          $existingSame = Passenger::where('booking_id', $bookingId)->where('survivor_number', $sn)->first();

          if ($existingSame && $existingSame->id !== $leadPassengerSlot->id) {
            $otherOrder = $existingSame->passenger_order;
            $oldLeadOrder = $leadPassengerSlot->passenger_order;
            $existingSame->lead_passenger = true;
            $existingSame->passenger_order = 1;
            $existingSame->save();
            $leadPassengerSlot->lead_passenger = false;
            $leadPassengerSlot->passenger_order = $otherOrder;
            $leadPassengerSlot->save();
            $booking->customer_id = $newLeadId;
            $booking->save();
            
            $passengers = $booking->passengers()->orderBy('passenger_order')->get();
            return response()->json(['passengers' => $passengers]);
          }
          $oldCost = $leadPassengerSlot->passenger_allocated_cost;
          $oldBalance = $leadPassengerSlot->passenger_balance;

          $leadPassengerSlot->fill(
            array_merge($passengerData, [
              'survivor_number' => $sn,
              'email' => $request->input('email', $leadPassengerSlot->email ?? $user->email),
              'lead_passenger' => true,
              'passenger_allocated_cost' => $oldCost,
              'passenger_balance' => $oldBalance,
            ])
          );
          $leadPassengerSlot->save();

          $booking->customer_id = $newLeadId;
          $booking->save();

          $passengers = $booking->passengers()->orderBy('passenger_order')->get();
          return response()->json(['passengers' => $passengers]);
        });
      });
    } catch (Exception $e) {
      $this->logException($e);
      return response()->json(['error' => 'Error switching lead passenger.'], 500);
    }
  }

  public function cabinUpgrade(Request $request)
  {
    try {
      $event_id = request()->route('id');

      $booking_id = $request->input('booking_id');
      $cabinNumber = $request->input('cabin_number');
      $cabinCategoryId = $request->input('cabin_category_id');
      $typeId = $request->input('cabin_type_id');
      $currentCapacity = $request->input('capacity');

      $selectedCabin = $this->bookingRepository->getBookableCabinByParams(
        $currentCapacity,
        $cabinNumber,
        $typeId,
        $cabinCategoryId
      );

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $selectedCabin) {
          $booking = Booking::find($booking_id);
          $oldBooking = clone $booking;
          $result = null;

          DB::transaction(function () use ($selectedCabin, $booking, &$result, $oldBooking) {
            $result = $this->bookingRepository->changeCabin($booking, $selectedCabin);

            $booking
              ->refresh()
              ->load([
                'cabin',
                'cabin.cabinSpec',
                'cabin.category',
                'cabin.cabinType',
                'adjustments',
                'passengers.discounts',
                'passengers.fees',
              ]);

            $this->paymentInfoService->syncAllocatedCost($booking);
          });

          $this->paymentInfoService->syncAllocatedCost($booking);

          return redirect()
            ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
            ->with('success', 'Cabin upgraded successfully.');
        },
        $event_id,
        $booking_id,
        $selectedCabin
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function switchPaymentPlan(Request $request)
  {
    $request->validate([
      'booking_id' => 'required|exists:bookings,id',
      'payment_plan' => 'required|in:PAY_IN_FULL,INSTALLMENTS',
      'number_of_installments' => 'nullable|integer|min:1',
    ]);

    $result = null;
    try {
      $booking = Booking::findOrFail($request->booking_id);
      $event_id = $booking->event_id;

      $number_of_installments = $request->input('number_of_installments', 1);

      $result = $this->bookingRepository->changePaymentPlan($booking, $request->payment_plan, $number_of_installments);

      if ($result) {
        session()->flash('success', 'Payment plan switched successfully.');
      } else {
        session()->flash('error', 'Error switching payment plan.');
      }
    } catch (Exception $e) {
      session()->flash('error', $e->getMessage());
    } finally {
      return Inertia::location(
        route('bookings.show', [
          'id' => $event_id,
          'booking_code' => $result?->booking_code ?? ($booking->booking_code ?? ''),
        ])
      );
    }
  }



  public function getBookingFinalCost(Request $request, Event $event)
  {
    $passenger = $request->input('passenger');
    $cabinCategory = CabinCategory::with('spec')->findOrFail($request->cabin_category_id);

    $adjustmentCodes = [SystemAdjustment::TAX->value]; // TAX is always applied

    // Single Ticket Fee
    if ((int) $request->cabin_type_id !== CabinType::PRIVATE_CABIN->value) {
      $adjustmentCodes[] = SystemAdjustment::SINGLE_TICKET_FEE->value;
    }

    // Carbon Offset
    if ($request->boolean('carbon_offset')) {
      $carbonOffsetCode = SystemAdjustment::carbonOffset(
        $cabinCategory->spec->category_type ? ucfirst($cabinCategory->spec->category_type[0]) : null
      );
      $adjustmentCodes[] = $carbonOffsetCode->value;
    }

    // Choose Your Cabin
    if ($request->boolean('you_choose_your_cabin')) {
      $adjustmentCodes[] = SystemAdjustment::CHOOSE_YOUR_CABIN->value;
    }

    // Paid In Full
    if ($request->payment_plan === 'PAY_IN_FULL') {
      $adjustmentCodes[] = SystemAdjustment::PAID_IN_FULL->value;
    }

    // Fetch all Fixed code based adjustments
    $selectedAdjustments = $this->adjustmentsRepository->getAdjustmentsByCodes($adjustmentCodes, $event->id);

    // Add Membership Level Adjustment based on Survivor Number
    if (!empty($passenger['survivor_number'])) {
      $membershipAdjustment = $this->adjustmentsRepository->getAdjustmentsBySurvivorNumber(
        $passenger['survivor_number'],
        $event->id
      );

      if ($membershipAdjustment) {
        $selectedAdjustments[] = $membershipAdjustment;
      }
    }

    // Validate adjustments applicability
    $context = [
      'cabin' => [
        'category_name' => $cabinCategory->category_name,
        'code' => $cabinCategory->spec->category_code ?? null,
        'capacity' => $request->cabin_capacity,
      ],
      'event' => [
        'id' => $event->id,
        'status' => $event->status,
      ],
    ];

    $selectedAdjustments->transform(function ($adjustment) use ($context) {
      if (method_exists($adjustment, 'shouldApply') && !$adjustment->shouldApply($context)) {
        $adjustment->value = 0;
      }
      return $adjustment;
    });

    // Get price calculation
    $priceCalc = PriceCalculation::calculatePricePerPassenger(
      (float) $cabinCategory->price,
      (int) $request->cabin_capacity,
      (int) $request->cabin_type_id === 1,
      $selectedAdjustments
    );

    // totalPassenger has no use with single cabins
    if ((int) $request->cabin_type_id !== 1) {
      unset($priceCalc['totalPassenger']);
    }

    return response()->json(['priceCalc' => $priceCalc]);
  }

  public function updateBedConfig(UpdateBedConfigRequest $request, Booking $booking)
  {
    try {
      $old = $booking->bed_config;
      $booking->bed_config = $request->validated()['bed_config'];
      
      $this->withPermission([Permissions::EditBookings], function($request, $old, $booking){
        $booking->save();
        return redirect()
        ->back()
        ->with('success', 'Bed configuration updated');
      }
      ,$request,$old,$booking);

    } catch (\Throwable $e) {
      \Log::error('Error updating bed config', [
        'booking_id' => $booking->id,
        'error' => $e->getMessage(),
      ]);
      return redirect()
        ->back()
        ->with('error', 'Failed to update bed configuration. Please try again.');
    }
  }

}
