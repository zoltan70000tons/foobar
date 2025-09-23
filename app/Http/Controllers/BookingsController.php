<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Interfaces\BookingInterface;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Interfaces\LogInterface;
use App\Interfaces\TeamRepositoryInterface;
use App\Models\Booking;
use App\Models\BookingAgentSessions;
use App\Models\Cabin;
use App\Models\CabinSpec;
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
use App\Traits\BookingLogTrait;
use App\Traits\CabinFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Services\PaymentInfoService;

class BookingsController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;
  use CabinFilter;
  use BookingLogTrait;

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
        // function ($event_id, $keyword, $tag) {
        //   $newBookings = $this->bookingRepository->getByStatus('NEW', $keyword);
        //   $inProgressBookings = $this->bookingRepository->getByStatus('ON HOLD', $keyword);
        //   $uploadedBookings = $this->bookingRepository->getByStatus('UPLOADED', $keyword);
        //   $cancelledBookings = $this->bookingRepository->getByStatus('CANCELLED', $keyword);
        //   $users = $this->teamRepository->getAllMembers(1);
        //   $cabinTypes = $this->cabinRepository->getTypes();
        //   $cabinCategories = $this->cabinCategoryRepository->getCategoriesByEvent(1);
        //   $tabIndex = 0;
        //   if (count($newBookings) > 0) {
        //     $tabIndex = 0;
        //   } elseif (count($inProgressBookings) > 0) {
        //     $tabIndex = 1;
        //   } elseif (count($uploadedBookings) > 0) {
        //     $tabIndex = 2;
        //   } elseif (count($cancelledBookings) > 0) {
        //     $tabIndex = 3;
        //   }

        //   //$cancelledBookings = [];
        //   $event = $this->eventRepository->find($event_id);
        //   return Inertia::render('Bookings/Index', [
        //     'event' => $event,
        //     'newBookings' => $newBookings,
        //     'inProgressBookings' => $inProgressBookings,
        //     'uploadedBookings' => $uploadedBookings,
        //     'cancelledBookings' => $cancelledBookings,
        //     'users' => $users,
        //     'cabinTypes' => $cabinTypes,
        //     'cabinCategories' => $cabinCategories,
        //     'tabIndex' => $tabIndex,
        //   ]);
        // },

        function ($event_id, $keyword, $tag) use ($status, $tab) {
          // THIS IS SLOW
          //$bookings = $this->bookingRepository->getByStatus($status, $keyword, ); // Only fetch one set

          $event = $this->eventRepository->find($event_id);
          $users = $this->teamRepository->getAllMembers(1);
          $cabinTypes = $this->cabinRepository->getTypes();
          $tags = $this->tagRepository->getAll();

          // THIS IS SLOW - FIXED
          $cabinCategories = $this->cabinCategoryRepository->getCategoriesByEvent(1);

          // Log everything
          // \Log::info('Event arr', ['event' => $event]);
          // \Log::info('Bookings arr', ['bookings' => $bookings]);
          // \Log::info('Users arr', ['users' => $users]);
          // \Log::info('CabinTypes arr', ['cabinTypes' => $cabinTypes]);
          // \Log::info('CabinCategories arr', ['cabinCategories' => $cabinCategories]);

          return Inertia::render('Bookings/Index', [
            'event' => $event,
            'bookings' => [],
            'users' => $users,
            'cabinTypes' => $cabinTypes,
            'cabinCategories' => $cabinCategories,
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

  public function create() {}

  public function store(Request $request)
  {
    $event_id = request()->route('id');

    $validated = $request->validate([
      'cabin_number' => ['required', 'string', 'exists:cabin_specs,cabin_number'],
      'cabin_category_id' => ['required', 'integer', 'exists:cabin_categories,id'],
      'payment_plan' => ['required', Rule::in(['INSTALLMENTS', 'PAY_IN_FULL'])],
      'carbon_offset' => ['required', 'boolean'],
      'number_of_installments' => ['nullable', 'integer', 'min:1', 'required_if:payment_plan,INSTALLMENTS'],
      'passenger.id' => ['required', 'string', 'exists:users,id'],
      'passenger.first_name' => ['required', 'string', 'max:255'],
      'passenger.middle_name' => ['nullable', 'string', 'max:255'],
      'passenger.last_name' => ['required', 'string', 'max:255'],
      'passenger.dob' => ['required', 'date', 'before:today'],
      'passenger.gender' => ['required', Rule::in(['M', 'F', 'O'])],
      'passenger.citizenship' => ['nullable', 'string', 'max:3'],
      'passenger.survivor_number' => [
        'required',
        'string',
        'regex:/^\d+$/',
        'exists:survivor_numbers,survivor_number',
        new UniqueSurvivorInEvent($event_id),
      ],
      'passenger.email' => ['required', 'email', 'email'],
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
      'passenger.lead_passenger' => ['required', 'boolean'],
      'passenger.travel_info' => ['required', 'boolean'],
      'passenger.terms_n_cons' => ['required', 'accepted'],
      'passenger.single_t_agreement' => [
        'required',
        'boolean',
        function ($attribute, $value, $fail) use ($request) {
          $cabinSpec = CabinSpec::query()->where('cabin_number', $request->input('cabin_number'))->first();

          if ($cabinSpec) {
            $cabin = Cabin::query()->find($cabinSpec->id);

            if ($cabin && in_array($cabin->cabin_type_id, [2, 3])) {
              if (!$value) {
                $fail('The STA must be checked when the cabin type Single');
              }
            }
          }
        },
      ],
      'passenger.newsletter' => ['nullable', 'boolean'],
      'passenger.passenger_allocated_cost' => ['nullable', 'numeric', 'min:0'],
      'passenger.passenger_balance' => ['nullable', 'numeric', 'min:0'],
      //'passenger.language' => ['nullable', 'string', 'max:2'],
    ]);

    try {
      $user = $request->user();
      $cabin_number = $validated['cabin_number'];
      $cabinCategoryId = $validated['cabin_category_id'];
      $passenger_data = $validated['passenger'];
      $passenger_data['cabin_conf_accp'] = true; //CCA is a required field, thus should go set as true by default - Nic
      $number_of_installments = $validated['number_of_installments'] ?? 1;
      $payment_plan = $validated['payment_plan'];
      $carbonOffset = $validated['carbon_offset'];

      return $this->withPermission(
        [Permissions::CreateBookings],
        function (
          $event_id,
          $cabin_number,
          $cabinCategoryId,
          $user,
          $passenger_data,
          $payment_plan,
          $number_of_installments,
          $carbonOffset
        ) {
          $cabin = Cabin::whereHas('cabinSpec', function ($query) use ($cabin_number, $cabinCategoryId) {
            $query->where('cabin_number', $cabin_number);
            $query->where('cabin_category_id', $cabinCategoryId);
          })->first();

          if ($cabin) {
            $bookingData = [
              'event_id' => $event_id,
              'customer_id' => $passenger_data['id'],
              'payment_plan' => $payment_plan,
              'number_of_installments' => $number_of_installments,
            ];
            $bookingData = $this->bookingRepository->createBooking($bookingData, $passenger_data, $cabin);
            $booking = $bookingData['booking'];
            $this->logRepository->writeOnBooking($booking->id, 'Booking created manually', $user);

            $adjustmentIds = [];

            if ($carbonOffset === true) {
              $code = 'CARBON_OFFSET';
              if ($cabin->category?->spec?->getFirstLetterOfCategoryType()) {
                $code .= '_' . $cabin->category?->spec?->getFirstLetterOfCategoryType();
              }

              $carbonOffsetFeeId = $this->adjustmentsRepository->getIdByCode($code);
              if ($carbonOffsetFeeId !== null) {
                $adjustmentIds[] = $carbonOffsetFeeId;
              }
            }

            //Since we are in the BookingsController, it is always a manual booking
            //Also, since we are in the if($cabin), we don't have to check if cabin was selected
            //Cabin selection is required in manual booking
            //Therefore we just add the cabin select fee every time
            $chooseYourCabinFeeId = $this->adjustmentsRepository->getIdByCode('CHOOSE_YOUR_CABIN');
            if ($chooseYourCabinFeeId !== null) {
              $adjustmentIds[] = $chooseYourCabinFeeId;
            }

            if ($payment_plan === 'PAY_IN_FULL') {
              $paidInFullDiscountId = $this->adjustmentsRepository->getIdByCode('PAID_IN_FULL');
              if ($paidInFullDiscountId !== null) {
                $adjustmentIds[] = $paidInFullDiscountId;
              }
            }

            //Since we are in the BookingsController, it is always a manual booking
            //Every manual booking has to have the TAX adjustment added
            $taxAddonId = $this->adjustmentsRepository->getIdByCode('TAX');
            if ($taxAddonId !== null) {
              $adjustmentIds[] = $taxAddonId;
            }

            if ($cabin->cabinType?->id === 2 || $cabin->cabinType?->id === 3) {
              $singleTicketFeeId = $this->adjustmentsRepository->getIdByCode('SINGLE_TICKET_FEE');
              if ($singleTicketFeeId !== null) {
                $adjustmentIds[] = $singleTicketFeeId;
              }
            }

            if (
              isset($passenger_data['survivor_number']) &&
              isset($passenger_data['lead_passenger']) &&
              $passenger_data['lead_passenger'] === true
            ) {
              $membershipLevelAdjustmentId = $this->adjustmentsRepository->getAdjustmentsBySurvivorNumber(
                $passenger_data['survivor_number']
              );

              if ($membershipLevelAdjustmentId) {
                $adjustmentIds[] = $membershipLevelAdjustmentId;
              }
            }

            DB::transaction(function () use ($adjustmentIds, $booking) {
              $this->adjustmentsRepository->attachAdjustments($adjustmentIds, $booking);

              $booking->refresh();

              $this->paymentInfoService->syncAllocatedCost($booking);
            });
          }
        },
        $event_id,
        $cabin_number,
        $cabinCategoryId,
        $user,
        $passenger_data,
        $payment_plan,
        $number_of_installments,
        $carbonOffset
      );
      return redirect()->back()->with('flash', [
        'message' => 'Booking created successfully.',
        'success' => true,
      ]);
    } catch (\Exception $e) {
      $this->logException($e);
      return redirect()->back()->with('flash', [
        'message' => 'Error creating booking.',
        'success' => false,
      ]);
    }
  }

  public function edit(Request $request) {}

  public function assignAgent(Request $request)
  {
    try {
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($request) {
          $request->validate([
            'agent_id' => 'required|exists:users,id',
            'booking_code' => 'required|exists:bookings,booking_code',
          ]);
          $agent_id = $request->input('agent_id');
          $booking_code = $request->input('booking_code');
          $booking = $this->bookingRepository->findByCode($booking_code);
          if ($booking) {
            $booking->agent_id = $agent_id;
            $booking->save();
            return redirect()->back()->with('message', 'User assigned successfully.');
          }
        },
        $request
      );
    } catch (\Exception $e) {
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
          $this->logRepository->writeOnBooking($booking->id, 'Updated Tag', $user);
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
    } catch (\Exception $e) {
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
          $adjustments = $this->adjustmentsRepository->listAdjustments();
          $cabinCategories = $this->cabinCategoryRepository->getCategoriesByEvent(1);
          $availableTags = Tag::type('booking')->orderBy('name')->get();
          $bookingId = $booking->id;

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
            'users' => $users,
            'isEditable' => $isEditable,
            'cabinTypes' => $cabinTypes,
            'cabinCategories' => $cabinCategories,
            'adjustments' => $adjustments,
            'availableTags' => $availableTags,
            'deletedPayments' => $deletedPayments,
          ]);
        },
        $event_id,
        $booking_code
      );
    } catch (Exception $e) {
      $this->logException($e);
    }
  }

  public function destroy(Cabin $cabin) {}

  public function addTag(Request $request) {}



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

    DB::table('booking_agent_sessions')
      ->where('booking_id', $bookingId)
      ->delete();


    DB::table('booking_agent_sessions')->insert([
      'booking_id' => $bookingId,
      'agent_id' => $user->id,
      'time' => now(),
    ]);

    broadcast(new \App\Events\BookingAgentSession(
      agentId: $user->id,
      bookingId: $bookingId,
      username: $user->username
    ));

    return Inertia::location(url()->previous());
  }

  // edit mode
  public function editMode(Request $request)
  {

    $bookingId = $request->input('booking_id');
    $eventId = $request->input('event_id');
    $lock = $request->input('lock') === '1';
    $user = Auth::user();

    if ($lock) {
      DB::table('booking_agent_sessions')->updateOrInsert(
        [
          'booking_id' => $bookingId,
          'agent_id' => $user->id
        ],
        [
          'time' => now()
        ]
      );
    } else {
      DB::table('booking_agent_sessions')
        ->where('booking_id', $bookingId)
        ->where('agent_id', $user->id)
        ->delete();
    }

    broadcast(new \App\Events\BookingAgentSession(
      agentId: $user->id,
      bookingId: $bookingId,
      username: $lock ? $user->username : null
    ));


    // // return inertia
    return $this->withPermission([Permissions::EditBookings], fn() => Inertia::location(url()->previous()));

    // try {
    //   $booking_id = $request->input('booking_id');
    //   $lock = $request->input('lock');
    //   $event_id = $request->input('event_id');

    //   broadcast(new \App\Events\BookingAgentSession(
    //     bookingId: $booking_id,
    //     username: $lock ? Auth::user()->username : null
    //   ));


    //   return $this->withPermission(
    //     [Permissions::EditBookings],
    //     function ($event_id, $booking_id, $lock) {
    //       $event = $this->eventRepository->find($event_id);
    //       $booking = Booking::find($booking_id);

    //       if ($lock == '1') {
    //         $bookingSession = new BookingAgentSessions();
    //         $bookingSession->agent_id = Auth::user()->id;
    //         $bookingSession->booking_id = $booking->id;
    //         $bookingSession->time = now();
    //         $bookingSession->save();

    //         return Inertia::location(url()->previous());
    //       } elseif ($lock == '0') {
    //         $bookingSession = BookingAgentSessions::where('booking_id', $booking_id)->first();
    //         if ($bookingSession) {
    //           $bookingSession->delete();
    //           return Inertia::location(url()->previous());
    //         }
    //       }

    //       return response()->json(
    //         [
    //           'success' => false,
    //           'message' => 'Invalid lock value.',
    //         ],
    //         400
    //       );
    //     },
    //     $event_id,
    //     $booking_id,
    //     $lock
    //   );
    // } catch (\Exception $e) {
    //   $this->logException($e);
    // }
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
    } catch (\Exception $e) {
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
    } catch (\Exception $e) {
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
    } catch (\Exception $e) {
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
    } catch (\Exception $e) {
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
      $balcony = $request->boolean('balcony');
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
        ->when($balcony, function ($collection) {
          return $collection->where('balcony', true);
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
    } catch (\Exception $e) {
      //throw $th;
    }
  }

  public function cancel(Request $request)
  {
    $request->validate([
      'booking_id' => 'required|exists:bookings,id',
    ]);

    $event_id = request()->route('id');
    $result = null; // <-- aquí

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
    } catch (\Exception $e) {
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

            $currentCabin = Cabin::with([
                'category.spec',
                'cabinSpec',
                'cabinType'
            ])
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

            $upgradeCabins = Cabin::with([
                'category.spec',
                'cabinSpec',
                'cabinType'
            ])
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
        } catch (\Exception $e) {
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
    $eventId   = $request->route('id');
    $bookingId = $request->route('booking_id');

    try {
      return $this->withPermission(
        [Permissions::EditBookings],
        function () use ($bookingId, $newLeadId, $eventId, $request) {

          return DB::transaction(function () use ($bookingId, $newLeadId, $request,$eventId) {

            $booking = Booking::with('passengers')->findOrFail($bookingId);
            $currentLead = $booking->passengers->firstWhere('lead_passenger', true);
            $currentSurvivor = SurvivorNumber::where('survivor_number', $currentLead->survivor_number)->first();
            $currentLeadUser = $currentSurvivor ? User::with('membershipTypes')->find($currentSurvivor->user_id) : null;
            $user    = User::with('survivorNumber')->findOrFail($newLeadId);
            $sn = $user->survivorNumber?->survivor_number;
            if (!$sn) {
              return response()->json(['error' => 'Selected user has no Survivor Number.'], 422);
            }
            
            $hasBooking = Passenger::checkSurvivorInActiveBookings($sn, $booking->event_id, $bookingId);
            if ($hasBooking) {
              return response()->json(['error' => 'Selected user is already in another active booking for this event.'], 422);
            } 

            if (!$user->hasRole('Customer')) {
              return response()->json(['error' => 'The selected user is not a valid lead passenger.'], 422);
            }
            $currentMaxMembership = $currentLeadUser?->membershipTypes->sortByDesc('booking_number_requirement')->first()?->booking_number_requirement;
            $userMax = $user->membershipTypes->sortByDesc('booking_number_requirement')->first()?->booking_number_requirement ?? null;
            $isLowerTier = $userMax && $currentMaxMembership ? $userMax < $currentMaxMembership : false;
            if ($isLowerTier) {
              return response()->json(['error' => 'The selected user has a lower membership tier than the current lead passenger.'], 422);
            }

            $leadPassengerSlot = Passenger::where('booking_id', $bookingId)
              ->where('lead_passenger', true)
              ->firstOrFail();

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
                'was_on_board'
              ] as $field
            ) {
              $passengerData[$field] = $request->has($field)
                ? filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN)
                : false;
            }
            $passengerData['terms_n_cons']   = true;
            $passengerData['cabin_conf_accp'] = true;


            // new in the same booking?
            $existingSame = Passenger::where('booking_id', $bookingId)
              ->where('survivor_number', $sn)
              ->first();

            if ($existingSame && $existingSame->id !== $leadPassengerSlot->id) {
              $otherOrder   = $existingSame->passenger_order;
              $oldLeadOrder = $leadPassengerSlot->passenger_order;
              $existingSame->lead_passenger   = true;
              $existingSame->passenger_order  = 1;
              $existingSame->save();
              $leadPassengerSlot->lead_passenger  = false;
              $leadPassengerSlot->passenger_order = $otherOrder;
              $leadPassengerSlot->save();
              $booking->customer_id = $newLeadId;
              $booking->save();

              $this->logRepository->writeOnBooking(
                $booking->id,
                'Lead passenger switched (same booking): from SN ' . $oldSurvivorNumber . ' to SN ' . $sn,
                $request->user()
              );

              $passengers = $booking->passengers()->orderBy('passenger_order')->get();
              return response()->json(['passengers' => $passengers]);
            }
            $oldCost    = $leadPassengerSlot->passenger_allocated_cost;
            $oldBalance = $leadPassengerSlot->passenger_balance;

            $leadPassengerSlot->fill(array_merge($passengerData, [
              'survivor_number' => $sn,
              'email'           => $request->input('email', $leadPassengerSlot->email ?? $user->email),
              'lead_passenger'  => true,
              'passenger_allocated_cost' => $oldCost,
              'passenger_balance'        => $oldBalance,
            ]));
            $leadPassengerSlot->save();

            $booking->customer_id = $newLeadId;
            $booking->save();

            $this->logRepository->writeOnBooking(
              $booking->id,
              'Lead passenger switched from SN ' . $oldSurvivorNumber . ' to SN ' . $sn,
              $request->user()
            );

            $passengers = $booking->passengers()->orderBy('passenger_order')->get();
            return response()->json(['passengers' => $passengers]);
          });
        }
      );
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

                        $booking->refresh()->load([
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

                    $this->saveBookingLog(
                        $booking->id,
                        'Cabin Upgrade',
                        "Upgrade from {$oldBooking->booking_code} to {$result->booking_code}"
                    );

                    return redirect()
                        ->route('bookings.show', ['id' => $event_id, 'booking_code' => $result->booking_code])
                        ->with('success', 'Cabin upgraded successfully.');
                },
                $event_id,
                $booking_id,
                $selectedCabin
            );
        } catch (\Exception $e) {
            //dd('Transaction failed', $e->getMessage(), $e->getTraceAsString());
            $this->logException($e);
        }
    }
}
