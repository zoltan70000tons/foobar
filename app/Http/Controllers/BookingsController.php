<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Events\BookingLocked;
use App\Interfaces\BookingInterface;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Interfaces\LogInterface;
use App\Interfaces\TeamRepositoryInterface;
use App\Models\Booking;
use App\Models\BookingAgentSessions;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Repositories\BookingRepository;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use App\Repositories\LogRepository;
use App\Repositories\TeamRepository;
use App\Traits\CabinFilter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Database\Seeders\BookingSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

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

  public function __construct(
    EventRepository $eventRepository,
    BookingRepository $bookingRepository,
    LogRepository $logRepository,
    TeamRepository $teamRepository,
    CabinRepository $cabinRepository,
    CabinCategoryRepository $cabinCategoryRepository
  ) {
    $this->eventRepository = $eventRepository;
    $this->bookingRepository = $bookingRepository;
    $this->logRepository = $logRepository;
    $this->teamRepository = $teamRepository;
    $this->cabinRepository = $cabinRepository;
    $this->cabinCategoryRepository = $cabinCategoryRepository;
  }
  public function index(Request $request)
  {
    try {
      $event_id = request()->route("id");
      $tag = $request->filled("tag") ? $request->tag : null;
      $keyword = $request->filled("keyword") ? $request->keyword : null;
      if ($event_id == "all") {
        $events = $this->eventRepository->getAll();
        return Inertia::render("Bookings/partials/Events", [
          "events" => $events,
        ]);
      }
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($event_id, $keyword, $tag) {
          $newBookings = $this->bookingRepository->getByStatus("NEW", $keyword);
          $inProgressBookings = $this->bookingRepository->getByStatus("ON HOLD", $keyword);
          $uploadedBookings = $this->bookingRepository->getByStatus("UPLOADED", $keyword);
          $users = $this->teamRepository->getAllMembers(1);
          $cancelledBookings = [];
          $event = $this->eventRepository->find($event_id);
          return Inertia::render("Bookings/Index", [
            "event" => $event,
            "newBookings" => $newBookings,
            "inProgressBookings" => $inProgressBookings,
            "uploadedBookings" => $uploadedBookings,
            "cancelledBookings" => $cancelledBookings,
            "users" => $users,
          ]);
        },
        $event_id,
        $keyword,
        $tag
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function create()
  {
  }

  public function store(Request $request)
  {
  }

  public function edit(Request $request)
  {
  }

  public function assignAgent(Request $request)
  {
    $request->validate([
      "agent_id" => "required|exists:users,id",
      "booking_code" => "required|exists:bookings,booking_code",
    ]);
    $agent_id = $request->input("agent_id");
    $booking_code = $request->input("booking_code");
    $booking = $this->bookingRepository->findByCode($booking_code);
    if ($booking) {
      $booking->agent_id = $agent_id;
      $booking->save();
      return redirect()->back()->with("message", "User assigned successfully.");
    }
  }

  public function update(Request $request)
  {
    try {
      $event_id = request()->route("id");
      $booking_code = request()->route("booking_code");
      $tags = $request->input("selectedTags");
      $user = $request->user();
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_code, $tags, $user) {
          $event = $this->eventRepository->find($event_id);
          $booking = $this->bookingRepository->findByCode($booking_code);
          $this->bookingRepository->update(["tags" => $tags], $booking->id);
          $this->logRepository->writeOnBooking($booking->id, "Updated Tag", $user);
          return Inertia::render("Bookings/partials/Show", [
            "event" => $event,
            "booking" => $booking,
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
      $event_id = request()->route("id");
      $booking_code = request()->route("booking_code");
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($event_id, $booking_code) {
          $event = $this->eventRepository->find($event_id);
          $booking = $this->bookingRepository->findByCode($booking_code);
          $isEditable = $booking->agent_id === Auth::user()->id;
          $users = $this->teamRepository->getAllMembers(1);
          $cabinTypes = $this->cabinRepository->getTypes();
          $cabinCategories = $this->cabinCategoryRepository->getCategoriesByEvent(1);
          return Inertia::render("Bookings/partials/Show", [
            "event" => $event,
            "booking" => $booking,
            "users" => $users,
            "isEditable" => $isEditable,
            "cabinTypes" => $cabinTypes,
            "cabinCategories" => $cabinCategories,
          ]);
        },
        $event_id,
        $booking_code
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function destroy(Cabin $cabin)
  {
  }

  public function addTag(Request $request)
  {
  }

  public function editMode(Request $request)
  {
    $booking_id = $request->input("booking_id");
    $lock = $request->input("lock");
    $event_id = $request->input("event_id");

    return $this->withPermission(
      [Permissions::EditBookings],
      function ($event_id, $booking_id, $lock) {
        $event = $this->eventRepository->find($event_id);
        $booking = Booking::find($booking_id);
        if ($lock == "1") {
          $bookingSession = new BookingAgentSessions();
          $bookingSession->agent_id = Auth::user()->id;
          $bookingSession->booking_id = $booking->id;
          $bookingSession->save();
        } elseif ($lock == "0") {
          $bookingSession = BookingAgentSessions::where("booking_id", $booking_id)->first();
          if ($bookingSession) {
            $bookingSession->delete();
          }
        }
      },
      $event_id,
      $booking_id,
      $lock
    );
  }

  public function statusUpdate(Request $request)
  {
    try {
      $event_id = request()->route("id");

      $booking_id = $request->input("booking_id");
      $status = $request->input("status");

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $status) {
          //$event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->changeStatus($booking, $status);
          if ($result) {
            return redirect()
              ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
              ->with("success", "Status updated successfully.");
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
      $event_id = request()->route("id");

      $booking_id = $request->input("booking_id");
      $cabin_number = $request->input("cabin_number");

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $cabin_number) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->changeCabin($booking, $cabin_number);
          if ($result) {
            return redirect()
              ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
              ->with("success", "Cabin updated successfully.");
          }
        },
        $event_id,
        $booking_id,
        $cabin_number
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function codeUpdate(Request $request)
  {
    try {
      $event_id = request()->route("id");
      $booking_id = $request->input("booking_id");
      $booking_code = $request->input("booking_code");

      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $booking_code) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->changeCode($booking, $booking_code);
          if ($result) {
            return redirect()
              ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
              ->with("success", "Booking code updated successfully.");
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
      $event_id = request()->route("id");
      $booking_id = $request->input("booking_id");
      $comment = $request->input("comment");
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $comment) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->addComment($booking, $comment);
          if ($result) {
            return redirect()
              ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
              ->with("success", "Comment added successfully.");
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
      $event_id = request()->route("id");
      $booking_id = $request->input("booking_id");
      $tags = $request->input("tags");
      return $this->withPermission(
        [Permissions::EditBookings],
        function ($event_id, $booking_id, $tags) {
          $event = $this->eventRepository->find($event_id);
          $booking = Booking::find($booking_id);
          $result = $this->bookingRepository->addTags($booking, $tags);
          if ($result) {
            return redirect()
              ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
              ->with("success", "Tags updated successfully.");
          }
        },
        $event_id,
        $booking_id,
        $tags
      );
    } catch (\Exception $e) {
      $this->logException($e);
    }
  }

  public function getAvailableCabins(Request $request)
  {
    $categoryId = $request->get("category_id");
    $typeId = $request->get("type_id");
    $deck = $request->get("deck");
    $balcony = $request->boolean("balcony");
    $location = $request->get("location");
    $accessible = $request->boolean("accessible");

    $cabinsData = $this->filterCabins($typeId, $categoryId);

    if (isset($cabinsData["error"])) {
      return response()->json(
        [
          "error" => $cabinsData["error"],
        ],
        $cabinsData["status"] ?? 404
      );
    }

    $filteredCabins = collect($cabinsData["cabins"])
      ->when($deck, function ($collection, $deck) {
        return $collection->where("deck", $deck);
      })
      ->when($balcony, function ($collection) {
        return $collection->where("balcony", true);
      })
      ->when($location, function ($collection, $location) {
        return $collection->where("location", $location);
      })
      ->when($accessible, function ($collection) {
        return $collection->where("accessible", true);
      });

    return response()->json([
      "cabins" => $filteredCabins->values()->all(),
    ]);
  }

  public function cancel(Request $request)
  {
    $request->validate([
      "booking_id" => "required|exists:bookings,id",
    ]);

    $event_id = request()->route("id");

    try {
      $booking = Booking::findOrFail($request->booking_id);
      if ($booking->status === "CANCELLED") {
        return response()->json(
          [
            "message" => "This booking is already cancelled.",
          ],
          400
        );
      }
      $result = $this->bookingRepository->cancel($booking);
      if ($result) {
        return redirect()
          ->route("bookings.show", ["id" => $event_id, "booking_code" => $result->booking_code])
          ->with("success", "Tags updated successfully.");
      }
    } catch (\Exception $e) {
      return response()->json(
        [
          "message" => "An error occurred while cancelling the booking.",
          "error" => $e->getMessage(),
        ],
        500
      );
    }
  }
}
