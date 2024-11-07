<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Interfaces\BookingInterface;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Repositories\BookingRepository;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Arr;

class BookingsController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

    protected EventRepositoryInterface $eventRepository;
    protected BookingInterface $bookingRepository;
    public function __construct(EventRepository $eventRepository, BookingRepository $bookingRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->bookingRepository = $bookingRepository;
    }
    public function index(Request $request)
    {
        try {
            $event_id = request()->route('id');
            $tag = $request->filled('tag') ? $request->tag : null;
            $keyword = $request->filled('keyword') ? $request->keyword : null;
            if ($event_id == 'all') {
                $events = $this->eventRepository->getAll();
                return Inertia::render('Bookings/partials/Events', [
                    'events' => $events
                ]);
            }
            return $this->withPermission([Permissions::ViewBookings], function ($event_id,$keyword, $tag) {
                $data = CabinCategory::all()->toArray();
                $newBookings = $this->bookingRepository->getByTag(['New'], $keyword);
                $inProgressBookings = $this->bookingRepository->getByTag(['New', 'UPLOADED', 'CANCELLED'],$keyword);
                $uploadedBookings = $this->bookingRepository->getByTag(['UPLOADED'],$keyword);
                $cancelledBookings = $this->bookingRepository->getByTag(['CANCELLED'],$keyword);
                $event = $this->eventRepository->find($event_id);
                return Inertia::render('Bookings/Index', [
                    'event' => $event,
                    'newBookings' => $newBookings,
                    'inProgressBookings' => $inProgressBookings,
                    'uploadedBookings' => $uploadedBookings,
                    'cancelledBookings' => $cancelledBookings
                ]);
            }, $event_id,$keyword,$tag);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create() {}


    public function store(Request $request) {}

    public function edit(Request $request) {}

    public function update(Request $request) {
        try {
            $event_id = request()->route('id');
            $booking_code = request()->route('booking_code');
            $status = $request->input('selectedTag'); 
            return $this->withPermission([Permissions::EditBookings], function ($event_id, $booking_code, $status) {
                $event = $this->eventRepository->find($event_id);
                
                $booking = $this->bookingRepository->findByCode($booking_code);
                $this->bookingRepository->update(array('status' => $status), $booking->id);

                // return Inertia::render('Bookings/partials/Show', [
                //     'event' => $event,
                //     'booking' =>$booking
                // ]);
            }, $event_id,$booking_code, $status);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function show(Cabin $cabin) {
        try {
            $event_id = request()->route('id');
            $booking_code = request()->route('booking_code');
            return $this->withPermission([Permissions::ViewBookings], function ($event_id, $booking_code) {
                $event = $this->eventRepository->find($event_id);
                
                $booking = $this->bookingRepository->findByCode($booking_code);
                return Inertia::render('Bookings/partials/Show', [
                    'event' => $event,
                    'booking' =>$booking
                ]);
            }, $event_id,$booking_code);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function destroy(Cabin $cabin) {}

    public function addTag(Request $request) {}

}
