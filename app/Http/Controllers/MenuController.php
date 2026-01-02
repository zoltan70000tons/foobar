<?php

namespace App\Http\Controllers;

use App\Interfaces\EventRepositoryInterface;
use App\Repositories\EventRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Inertia\Inertia;

class MenuController extends Controller {
    use HandlePermissions;
    use ExceptionLogger;

    protected EventRepositoryInterface $eventRepository;

    public function __construct(EventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }
    public function index() {
    }

    public function getEvents() {
        $events = $this->eventRepository->listMenu();
        return response()->json($events);
    }
}
