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
    public function __construct(EventRepository $eventRepository,BookingRepository $bookingRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->bookingRepository = $bookingRepository;
    }
    public function index()
    {
        try {
            $event_id = request()->route('id');
            if($event_id == 'all'){
                $events = $this->eventRepository->getAll();
                return Inertia::render('Bookings/partials/Events', [
                    'events' => $events
                ]);
            }
            return $this->withPermission([Permissions::ViewCabinCategories], function ($event_id) {
                $data = CabinCategory::all()->toArray();
                $bookings = $this->bookingRepository->getAll();
                $event = $this->eventRepository->find($event_id);
                return Inertia::render('Bookings/Index', [
                    'event' => $event,
                    'bookings' => $bookings
                ]);
            }, $event_id);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create()
    {
       
    }


    public function store(Request $request) {
        
    }

    public function edit(Request $request)
    {
        
    }

    public function update(Request $request)
    {

    }

    public function show(Cabin $cabin)
    {
       
    }

    public function destroy(Cabin $cabin)
    {
       
    }

    public function addTag(Request $request)
    {
       
    }

    public function updateStatus(Request $request)
    {
        
    }
}
