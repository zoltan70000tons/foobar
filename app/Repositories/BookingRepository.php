<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Helpers\InstallmentHelper;
use App\Interfaces\BookingInterface;
use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Services\PaymentInfoService;
use App\Traits\CabinFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Models\BookingLog;
use App\Models\Cabin;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\TemporaryReservation;
use App\Traits\BookingLogTrait;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PassengerRepository;
use App\Repositories\AdjustmentsRepository;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB as FacadesDB;

class BookingRepository implements BookingInterface
{
    use BookingLogTrait;
    use CabinFilter;

    public PassengerInterface $passengerRepository;
    public AdjustmentsRepository $adjustmentsRepository;
    public PaymentService $paymentService;
    public PaymentInfoService $paymentInfoService;

    public function __construct(
        PassengerRepository $passengerRepository,
        AdjustmentsRepository $adjustmentsRepository,
        PaymentService $paymentService,
        PaymentInfoService $paymentInfoService
    ) {
        $this->passengerRepository = $passengerRepository;
        $this->adjustmentsRepository = $adjustmentsRepository;
        $this->paymentService = $paymentService;
        $this->paymentInfoService = $paymentInfoService;
    }

    function getAll()
    {
        return Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail'])->get();
    }

    function getByTag($tags, $keyword = null)
    {
        $query = Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail', 'passengers'])
            ->withSum('passengers as balance', 'passenger_balance')
            ->withSum('passengers as cost', 'passenger_allocated_cost');

        if (!empty($tags)) {
            $query->where(function ($query) use ($tags) {
                foreach ($tags as $tag) {
                    $query->orWhereRaw(
                        'EXISTS (SELECT 1 FROM jsonb_array_elements_text(bookings.tags) as t WHERE LOWER(t) ILIKE ?)',
                        ['%' . strtolower($tag) . '%']
                    );
                }
            });
        }

        if (!empty($keyword)) {
            $keyword = strtolower($keyword);
            $query->where(function ($query) use ($keyword) {
                $query->orWhere(DB::raw('LOWER(booking_code)'), 'like', '%' . $keyword . '%');

                $query->orWhereHas('customer.detail', function ($query) use ($keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query
                            ->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
                            ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
                            ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
                    });
                });

                $query->orWhereHas('cabin.cabinType', function ($query) use ($keyword) {
                    $query->where(DB::raw('LOWER(cabin_type)'), 'like', '%' . $keyword . '%');
                });

                $query->orWhereHas('passengers', function ($query) use ($keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query
                            ->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
                            ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
                            ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
                    });
                });
            });
        }

        $results = $query->get();
        $results->each(function ($booking) {
            $booking->fullName = $booking->customer->detail->full_name ?? null;
            $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;

            if ($booking->passengers) {
                $booking->passengers = $booking->passengers->sortByDesc('lead_passenger')->values();
            }
            $booking->subRows = $booking->passengers ?? [];
        });

        return $results;
    }

    function getByStatus(
        $eventId,
        $status,
        $keyword = null,
        ?int $perPage = 10,
        ?string $sortKey = 'created_at',
        ?string $sortDirection = 'desc',
        $tags = [],
        $user_ids = [],
        $dateRange = null
    ) {
        $query = Booking::with([
            'cabin',
            'cabin.cabinType',
            'customer',
            'customer.detail',
            'passengers' => fn($q) => $q->orderBy('passenger_order'),
            'passengers.installments',
            'passengers.payments',
            'agent',
            'agent.detail',
        ])
            ->withSum('passengers as balance', 'passenger_balance')
            ->withSum('passengers as cost', 'passenger_allocated_cost')
            ->where('event_id', $eventId)
            ->where('status', $status);

        if (!empty($keyword)) {
            $keyword = strtolower($keyword);

            $query->where(function ($query) use ($keyword) {
                $query->orWhere(DB::raw('LOWER(booking_code)'), 'like', '%' . $keyword . '%')
                    ->orWhereHas('customer.detail', function ($query) use ($keyword) {
                        $query->where(function ($query) use ($keyword) {
                            $query
                                ->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
                                ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
                                ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
                        });
                    })
                    ->orWhereHas('cabin.cabinType', function ($query) use ($keyword) {
                        $query->where(DB::raw('LOWER(cabin_type)'), 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('passengers', function ($query) use ($keyword) {
                        $query->where(function ($query) use ($keyword) {
                            $query
                                ->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
                                ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
                                ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
                        });
                    })
                    ->orWhereHas('agent', function ($query) use ($keyword) {
                        $query->where(DB::raw('LOWER(username)'), 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (!empty($tags)) {
            $query->whereExists(function ($sub) use ($tags) {
                $sub->select(DB::raw(1))
                    ->from('taggings as tg')
                    ->whereColumn('tg.entity_id', DB::raw('bookings.id::text'))
                    ->where('tg.entity_type', 'booking')
                    ->whereIn('tg.tag_id', $tags);
            });
        }

        if (!empty($user_ids)) {
            $query->whereIn('agent_id', $user_ids);
        }

        $advancedSorts = ['longestDueDateInstallment', 'cabinType', 'fullName'];

        if (!empty($sortKey) && !in_array($sortKey, $advancedSorts)) {
            $query->orderBy($sortKey, $sortDirection ?? 'desc');
        }

        $results = $query->paginate($perPage, ['*'], 'page', request()->get('page', 1));

        if (in_array($sortKey, $advancedSorts) && $results instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $collection = $results->getCollection()->sortBy(function ($booking) use ($sortKey) {
                return match ($sortKey) {
                    'longestDueDateInstallment' => Carbon::parse(
                        InstallmentHelper::getFirstUnpaidInstallmentForBooking($booking) ?? now()->addYears(1000)
                    ),
                    'cabinType' => $booking?->cabin?->cabinType?->id ?? 0,
                    'fullName' => $booking->passengers->firstWhere('passenger_order', 1)?->first_name ?? '',
                };
            });

            if ($sortDirection === 'desc') {
                $collection = $collection->reverse();
            }

            $results->setCollection($collection->values());
        }

        if ($dateRange && isset($dateRange['longestDueDateInstallment'])) {
            $startDate = Carbon::parse($dateRange['longestDueDateInstallment']['startDate']);
            $endDate = isset($dateRange['longestDueDateInstallment']['endDate'])
                ? Carbon::parse($dateRange['longestDueDateInstallment']['endDate'])
                : null;

            $filtered = $results->getCollection()->filter(function ($booking) use ($startDate, $endDate) {
                $dueDate = InstallmentHelper::getFirstUnpaidInstallmentForBooking($booking);
                if (!$dueDate) return false;

                $dueDate = Carbon::parse($dueDate);
                return $dueDate->gte($startDate) && (!$endDate || $dueDate->lte($endDate));
            })->values();

            $results = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered,
                $filtered->count(),
                $results->perPage(),
                $results->currentPage(),
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        }

        $results->getCollection()->each(function ($booking) {
            $booking->fullName = $booking->customer->detail->full_name ?? null;
            $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;
            $booking->longestDueDateInstallment = InstallmentHelper::getFirstUnpaidInstallmentForBooking($booking);

            $booking->passengers->each(function ($passenger) {
                $passenger->setAttribute('installment_status', $passenger->installment_status);
            });

            $booking->editingUsername = DB::table('booking_agent_sessions')
                ->where('booking_id', $booking->id)
                ->join('users', 'booking_agent_sessions.agent_id', '=', 'users.id')
                ->value('username');

            $booking->subRows = $booking->passengers ?? [];
        });

        return $results;
    }

    function find($id)
    {
        return Booking::find($id);
    }

    function findByCode($code)
    {
        $booking = Booking::with([
            'cabin',
            'cabin.cabinType',
            'cabin.category',
            'adjustments',
            'passengers' => function ($query) {
                $query->orderBy('passenger_order', 'asc');
            },
            'passengers.installments',
            'passengers.payments' => function ($query) {
                $query->with(['paymentTransferFrom', 'paymentTransferTo']);
            },
            'passengers.fees',
            'passengers.discounts',
            'passengers.onboardCredits',
            'passengers.passengerInvitation',
            'logs',
            'logs.user',
            'lockedBy',
            'lockedBy.agent',
            'comments',
            'comments.user',
            'agent',
            'tags'
        ])
            ->where('booking_code', '=', $code)
            ->first();
        $booking->passengers->each(function ($passenger) {
            $passenger->setAttribute('installment_status', $passenger->installment_status);
        });

        return $booking;
    }

    function save(array $data): ?Booking
    {
        return new Booking();
    }

    function update(array $data, $id)
    {
        $booking = $this->find($id);
        $tags = $data['tags'];
        $booking->tags = $tags;
        $booking->save();
    }

    function delete($id) {}

    function assignAgent($code, $user)
    {
        //Log::info($code);
        //Log::info($user);
        //dd('ok');
    }

    function addTags($booking, $tags)
    {

        try {
            $tag = Tag::type('booking')->whereIn('id', $tags)->get();
            $booking->tags()->sync($tag);
            // if (!is_array($tags)) {
            //     throw new InvalidArgumentException('Tags must be an array.');
            // }
            // $uniqueTags = array_unique($tags);
            // $originalTags = $booking->tags;

            // $booking->update([
            //     'tags' => $uniqueTags,
            // ]);

            // if ($originalTags !== $uniqueTags) {
            //     $this->saveBookingLog(
            //         $booking->id,
            //         'Changed booking tags',
            //         sprintf(
            //             'Booking tags changed from [%s] to [%s].',
            //             implode(', ', is_array($originalTags) ? $originalTags : []),
            //             implode(', ', is_array($uniqueTags) ? $uniqueTags : [])
            //         )
            //     );
            // }



            return $booking;
        } catch (\Throwable $e) {
            \Log::error("Failed to update tags for booking ID {$booking->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    function changeCabin(Booking $booking, $cabin_number)
    {
        $result = $booking->changeCabin($cabin_number);
        if (is_array($result) && array_key_exists('error', $result)) {
            return $booking;
        } else {
            return $result;
        }
    }

    function changeCode(Booking $booking, $new_code)
    {
        try {
            if (empty($new_code)) {
                throw new InvalidArgumentException('The new booking code cannot be empty.');
            }

            if (Booking::where('booking_code', $new_code)->exists()) {
                throw new InvalidArgumentException('The new booking code is already in use.');
            }
            $originalCode = $booking->booking_code;
            $booking->booking_code = $new_code;
            $booking->save();
            $this->saveBookingLog(
                $booking->id,
                'Changed booking code',
                "Booking code changed manually from {$originalCode} to {$new_code}."
            );

            return $booking;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changeStatus(Booking $booking, $status)
    {
        try {
            if (empty($status)) {
                throw new InvalidArgumentException('The status field cannot be empty.');
            }
            $originalStatus = $booking->status;
            $booking->status = $status;
            $booking->save();
            $this->saveBookingLog(
                $booking->id,
                'Changed booking status',
                "Booking status changed from {$originalStatus} to {$status}."
            );
            return $booking;
        } catch (\Exception $e) {
            Log::info($e);
            return false;
        }
    }

    public function addComment(Booking $booking, $comment)
    {
        try {
            if (empty($comment)) {
                throw new InvalidArgumentException('The comment field cannot be empty.');
            }
            $sanitizedComment = htmlspecialchars(strip_tags($comment));
            $formattedComment = ucfirst($sanitizedComment);
            Comment::create([
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'comment' => $formattedComment,
            ]);
            return $booking;
        } catch (\Exception $e) {
            Log::info($e);
            return false;
        }
    }

    public function cancel(Booking $booking)
    {
        try {
            $booking->cancel();
            $this->saveBookingLog($booking->id, 'Cancelled', 'The booking was cancelled');
            return $booking;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Creates a new booking in the system, linking a passenger and a cabin.
     *
     * @param array $bookingData Data for creating the booking (e.g., dates, status).
     * @param array|null $passengerData Data for creating the passenger.
     * @param Cabin|null $cabin An instance of the Cabin model to associate with the booking (optional).
     * @param int|null $temporaryBookingId The ID of a temporary booking to convert into a booking (optional).
     *
     * @throws InvalidArgumentException If neither a Cabin object nor a Reservation ID is provided.
     * @throws \Exception If the reservation or cabin associated with the Reservation ID is not found.
     * @throws \Exception if The cabin is not available.;
     *
     * @return array An array containing either:
     *               - Success: ['message' => string, 'booking' => Booking, 'passenger' => Passenger|null]
     *               - Error: ['error' => true, 'message' => string]
     */
    public function createBooking(
        array $bookingData,
        $passengerData,
        ?Cabin $cabin = null,
        ?int $temporaryBookingId = null
    ): array {
        if (is_null($cabin) && is_null($temporaryBookingId)) {
            throw new InvalidArgumentException('You must provide a Cabin object or a Temporary Booking ID.');
        }

        FacadesDB::beginTransaction();
        try {
            if ($temporaryBookingId) {
                $tempReservation = TemporaryReservation::find($temporaryBookingId);
                if (!$tempReservation) {
                    throw new \Exception('Temporary booking ID not found.');
                }

                Log::info('1. Temp reservation found', ['tempReservation' => $tempReservation]);

                $cabinId = $tempReservation->cabin_id;
                $selectedCabin = Cabin::find($cabinId);

                Log::info('2. selectedCabin', ['selectedCabin' => $selectedCabin]);

                if (!$selectedCabin) {
                    throw new \Exception('Cabin not found for the given reservation ID.');
                }
            } else {
                $selectedCabin = $cabin;
            }
            if (!$selectedCabin) {
                throw new \Exception('Cabin not found.');
                // CC: JG Do we need the logic below?
                $availableCabins = $this->filterCabins(
                    $selectedCabin->cabin_type_id,
                    $selectedCabin->cabin_category_id,
                    null,
                    true
                );

                Log::info('3. availableCabins', ['availableCabins' => $availableCabins]);

                if (is_array($availableCabins) && array_key_exists('error', $availableCabins)) {
                    throw new \Exception($availableCabins['error']);
                }
                $availableCabins = $availableCabins['cabins']->toArray();
                $cabinNumberToSearch = $selectedCabin->cabin_number;
                $cabinNumbers = array_column($availableCabins, 'cabin_number');
                $available = array_search($cabinNumberToSearch, $cabinNumbers) !== false;
                if (!$available) {
                    throw new \Exception('Cabin not available.');
                }
            }

            $booking = new Booking();
            $booking->fill($bookingData);
            $booking->cabin_id = $selectedCabin->id;
            unset($booking->number_of_installments);
            $booking->save();
            $passenger = null;

            if ($passengerData) {
                $passengerData['number_of_installments'] = $bookingData['number_of_installments'] ?? null;
                $passenger = $this->passengerRepository->create($passengerData, $booking);
            }

            // JG ---- start Installments
            if ($passenger && $bookingData['number_of_installments'] >= 1) {
                // get passenger who lead_passenger have true
                $passId = $passenger->id;
                $installments = (int) $bookingData['number_of_installments'];

                $this->paymentService->createInstallments($passId, $installments);
            }
            // JG  ---- end Installments

            if ($passenger && $booking) {
                // JG  ---- start Create adjustments
                $adjustmentIds = collect($passengerData['addons'] ?? [])
                    ->filter(fn($addon) => isset($addon['id']))
                    ->map(fn($addon) => $addon['id'])
                    ->all();

                $membershipLevelAdjustmentId = $this->adjustmentsRepository->getAdjustmentsBySurvivorNumber(
                    $passengerData['survivor_number']
                );

                // Check if the adjustment is already in the list
                if ($membershipLevelAdjustmentId && !in_array($membershipLevelAdjustmentId, $adjustmentIds)) {
                    $adjustmentIds[] = $membershipLevelAdjustmentId;
                }

                $this->adjustmentsRepository->attachAdjustments($adjustmentIds, $booking);
                // JG  ---- end Create adjustments

                if ($temporaryBookingId) {
                    TemporaryReservation::find($temporaryBookingId)?->delete();
                }

                $this->paymentInfoService->syncAllocatedCost($booking);

                DB::commit();

                return [
                    'message' => 'Booking created successfully.',
                    'booking' => $booking,
                    'passenger' => $passenger,
                ];
            }
            Log::info($booking);
            Log::info($passenger);
            throw new \Exception('Error creating booking.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            FacadesDB::rollBack();
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
