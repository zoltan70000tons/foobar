<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Models\BookingLog;
use App\Models\Cabin;
use App\Models\Comment;
use App\Models\TemporaryReservation;
use App\Traits\BookingLogTrait;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PassengerRepository;
use Illuminate\Support\Facades\DB as FacadesDB;

class BookingRepository implements BookingInterface
{
  use BookingLogTrait;

  protected PassengerInterface $passengerRepository;

  public function __construct(PassengerRepository $passengerRepository)
  {
    $this->passengerRepository = $passengerRepository;
  }

  function getAll()
  {
    return Booking::with(["cabin", "cabin.cabinType", "customer", "customer.detail"])->get();
  }

  function getByTag($tags, $keyword = null)
  {
    $query = Booking::with(["cabin", "cabin.cabinType", "customer", "customer.detail", "passengers"])
      ->withSum("passengers as balance", "passenger_balance")
      ->withSum("passengers as cost", "passenger_allocated_cost");

    if (!empty($tags)) {
      $query->where(function ($query) use ($tags) {
        foreach ($tags as $tag) {
          $query->orWhereRaw(
            "EXISTS (SELECT 1 FROM jsonb_array_elements_text(bookings.tags) as t WHERE LOWER(t) ILIKE ?)",
            ["%" . strtolower($tag) . "%"]
          );
        }
      });
    }

    if (!empty($keyword)) {
      $keyword = strtolower($keyword);

      $query->where(function ($query) use ($keyword) {
        $query->orWhere(DB::raw("LOWER(booking_code)"), "like", "%" . $keyword . "%");

        $query->orWhereHas("customer.detail", function ($query) use ($keyword) {
          $query->where(function ($query) use ($keyword) {
            $query
              ->where(DB::raw("LOWER(first_name)"), "like", "%" . $keyword . "%")
              ->orWhere(DB::raw("LOWER(last_name)"), "like", "%" . $keyword . "%")
              ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), "like", "%" . $keyword . "%");
          });
        });

        $query->orWhereHas("cabin.cabinType", function ($query) use ($keyword) {
          $query->where(DB::raw("LOWER(cabin_type)"), "like", "%" . $keyword . "%");
        });

        $query->orWhereHas("passengers", function ($query) use ($keyword) {
          $query->where(function ($query) use ($keyword) {
            $query
              ->where(DB::raw("LOWER(first_name)"), "like", "%" . $keyword . "%")
              ->orWhere(DB::raw("LOWER(last_name)"), "like", "%" . $keyword . "%")
              ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), "like", "%" . $keyword . "%");
          });
        });
      });
    }

    $results = $query->get();
    $results->each(function ($booking) {
      $booking->fullName = $booking->customer->detail->full_name ?? null;
      $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;
      $booking->subRows = $booking->passengers ?? [];
    });

    return $results;
  }

  function getByStatus($status, $keyword = null)
  {
    $query = Booking::with([
      "cabin",
      "cabin.cabinType",
      "customer",
      "customer.detail",
      "passengers",
      "agent",
      "agent.detail",
    ])
      ->withSum("passengers as balance", "passenger_balance")
      ->withSum("passengers as cost", "passenger_allocated_cost")
      ->where("status", "=", $status);

    $results = $query->get();
    $results->each(function ($booking) {
      $booking->fullName = $booking->customer->detail->full_name ?? null;
      $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;
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
    return Booking::with(
      "cabin",
      "cabin.cabinType",
      "cabin.cabinCategory",
      "passengers",
      "logs",
      "logs.user",
      "lockedBy",
      "comments",
      "comments.user",
      "agent"
    )
      ->where("booking_code", "=", $code)
      ->first();
  }

  function save(array $data): ?Booking
  {
    return new Booking();
  }

  function update(array $data, $id)
  {
    $booking = $this->find($id);
    $tags = $data["tags"];
    $booking->tags = $tags;
    $booking->save();
  }

  function delete($id)
  {
  }

  function assignAgent($code, $user)
  {
    Log::info($code);
    Log::info($user);
    dd("ok");
  }

  function addTags($booking, $tags)
  {
    try {
      if (!is_array($tags)) {
        throw new InvalidArgumentException("Tags must be an array.");
      }

      $originalTags = $booking->tags;

      $booking->update([
        "tags" => $tags,
      ]);

      if ($originalTags !== $tags) {
        $this->saveBookingLog(
          $booking->id,
          "Changed booking tags",
          sprintf("Booking tags changed from [%s] to [%s].", implode(", ", $originalTags ?? []), implode(", ", $tags))
        );
      }

      return $booking;
    } catch (\Throwable $e) {
      \Log::error("Failed to update tags for booking ID {$booking->id}: {$e->getMessage()}");
      throw $e;
    }
  }

  function changeCabin(Booking $booking, $cabin_number)
  {
    $booking = $booking->changeCabin($booking, $cabin_number);
    if ($booking) {
      $cabin = $booking->cabin;
      $this->saveBookingLog(
        $booking->id,
        "Changed cabin number",
        "Cabin number changed from {$cabin->cabin_number} to {$cabin_number}."
      );
    }
    return $booking;
  }

  function changeCode(Booking $booking, $new_code)
  {
    try {
      if (empty($new_code)) {
        throw new InvalidArgumentException("The new booking code cannot be empty.");
      }

      if (Booking::where("booking_code", $new_code)->exists()) {
        throw new InvalidArgumentException("The new booking code is already in use.");
      }
      $originalCode = $booking->booking_code;
      $booking->booking_code = $new_code;
      $booking->save();
      $this->saveBookingLog(
        $booking->id,
        "Changed booking code",
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
        throw new InvalidArgumentException("The status field cannot be empty.");
      }
      $originalStatus = $booking->status;
      $booking->status = $status;
      $booking->save();
      $this->saveBookingLog(
        $booking->id,
        "Changed booking status",
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
        throw new InvalidArgumentException("The comment field cannot be empty.");
      }
      $sanitizedComment = htmlspecialchars(strip_tags($comment));
      $formattedComment = ucfirst($sanitizedComment);
      Comment::create([
        "booking_id" => $booking->id,
        "user_id" => Auth::id(),
        "comment" => $formattedComment,
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
      $this->saveBookingLog($booking->id, "Cancelled", "The booking was cancelled");
      return $booking;
    } catch (\Exception $e) {
      return false;
    }
  }

  public function createBooking(
    array $bookingData,
    $passengerData,
    ?Cabin $cabin = null,
    ?int $reservation_id = null
  ): array {
    if (is_null($cabin) && is_null($reservation_id)) {
      throw new InvalidArgumentException("You must provide a Cabin object or a Reservation Id.");
    }

    \Log::info("Creating booking with data:", $bookingData);

    \Log::info("Passenger data:", $passengerData);

    FacadesDB::beginTransaction();
    try {
      $selectedCabin = null;

      // Handle temporary reservation case
      if ($reservation_id) {
        \Log::info("Reservation ID provided: {$reservation_id}");
        $tempReservation = TemporaryReservation::find($reservation_id);
        if (!$tempReservation) {
          throw new \Exception("Temporary reservation not found.");
        }

        $cabinId = $tempReservation->cabin_id;
        \Log::info("Temporary reservation found, cabin ID: {$cabinId}");
        $selectedCabin = Cabin::find($cabinId);
        if (!$selectedCabin) {
          throw new \Exception("Cabin not found for the given reservation ID.");
        }
      } else {
        // Use provided cabin
        $selectedCabin = $cabin;
        \Log::info("Using provided cabin:", $cabin->toArray());
      }

      // Create booking
      $booking = new Booking();
      $booking->fill($bookingData);
      $booking->cabin_id = $selectedCabin->id;
      $booking->status = $bookingData["status"] ?? "NEW";
      $booking->save();
      \Log::info("Booking saved:", $booking->toArray());

      // Create passenger
      $passenger = null;
      if ($passengerData) {
        $passenger = $this->passengerRepository->create($passengerData, $booking);
        \Log::info("Passenger created:", $passenger->toArray());
      }

      // Handle cleanup
      if ($passenger && $booking) {
        if ($reservation_id) {
          TemporaryReservation::find($reservation_id)?->delete();
          \Log::info("Temporary reservation deleted: {$reservation_id}");
        }

        FacadesDB::commit();
        return [
          "message" => "Booking created successfully.",
          "booking" => $booking,
          "passenger" => $passenger,
        ];
      }

      throw new \Exception("Error creating booking: Booking or Passenger not created properly.");
    } catch (\Exception $e) {
      FacadesDB::rollBack();
      \Log::error("Error creating booking:", [
        "message" => $e->getMessage(),
        "trace" => $e->getTraceAsString(),
      ]);

      return [
        "error" => true,
        "message" => $e->getMessage(),
      ];
    }
  }
}
