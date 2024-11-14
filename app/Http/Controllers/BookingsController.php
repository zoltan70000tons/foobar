<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Interfaces\BookingInterface;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Interfaces\LogInterface;
use App\Interfaces\TeamRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Repositories\BookingRepository;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use App\Repositories\LogRepository;
use App\Repositories\TeamRepository;
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
    protected LogInterface $logRepository;
    protected TeamRepositoryInterface $teamRepository;

    public function __construct(EventRepository $eventRepository, 
    BookingRepository $bookingRepository, LogRepository $logRepository, TeamRepository $teamRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->bookingRepository = $bookingRepository;
        $this->logRepository = $logRepository;
        $this->teamRepository = $teamRepository;
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

                $newBookings = $this->bookingRepository->getByStatus('NEW', $keyword);
                $inProgressBookings = $this->bookingRepository->getByStatus('ON HOLD',$keyword);
                $uploadedBookings = $this->bookingRepository->getByStatus('UPLOADED',$keyword);
                $users = $this->teamRepository->getAllMembers(1);
                $cancelledBookings = [];
                $event = $this->eventRepository->find($event_id);
                return Inertia::render('Bookings/Index', [
                    'event' => $event,
                    'newBookings' => $newBookings,
                    'inProgressBookings' => $inProgressBookings,
                    'uploadedBookings' => $uploadedBookings,
                    'cancelledBookings' => $cancelledBookings,
                    'users' => $users
                ]);
            }, $event_id,$keyword,$tag);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create() {}


    public function store(Request $request) {}

    public function edit(Request $request) {}

    public function assignAgent(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,id',
            'booking_code' => 'required|exists:bookings,booking_code'
        ]);
        $agent_id = $request->input('agent_id');
        $booking_code = $request->input('booking_code');
        $booking = $this->bookingRepository->findByCode($booking_code);
        if($booking){
            $booking->agent_id = $agent_id;
            $booking->save();
            return redirect()->back()->with('message', 'User assigned successfully.');
        }
        
    }

    public function update(Request $request) {
        try {
            $event_id = request()->route('id');
            $booking_code = request()->route('booking_code');
            $tags = $request->input('selectedTags'); 
            $user = $request->user();
            return $this->withPermission([Permissions::EditBookings], function ($event_id, $booking_code, $tags,$user) {
                $event = $this->eventRepository->find($event_id);
                $booking = $this->bookingRepository->findByCode($booking_code);
                $this->bookingRepository->update(array('tags' => $tags), $booking->id);
                $this->logRepository->writeOnBooking($booking->id, 'Updated Tag', $user);
                return Inertia::render('Bookings/partials/Show', [
                    'event' => $event,
                    'booking' =>$booking
                ]);
            }, $event_id,$booking_code, $tags, $user);
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
