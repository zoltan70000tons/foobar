<?php

namespace App\Repositories;

use App\Enums\StatusCabin;
use App\Interfaces\BookingInterface;
use App\Interfaces\PassengerInterface;
use App\Models\Booking;
use App\Traits\CabinFilter;
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
use App\Repositories\AdjustmentsRepository;
use Illuminate\Support\Facades\DB as FacadesDB;

class BookingRepository implements BookingInterface
{
  use BookingLogTrait;
  use CabinFilter;

  protected PassengerInterface $passengerRepository;
  protected AdjustmentsRepository $adjustmentsRepository;

  public function __construct(PassengerRepository $passengerRepository, AdjustmentsRepository $adjustmentsRepository)
  {
    $this->passengerRepository = $passengerRepository;
    $this->adjustmentsRepository = $adjustmentsRepository;
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
    return Booking::with([
      "cabin",
      "cabin.cabinType",
      "cabin.category",
      "passengers" => function ($query) {
        $query->orderBy("id", "asc");
      },
      "logs",
      "logs.user",
      "lockedBy",
      "comments",
      "comments.user",
      "agent",
    ])
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
      throw new InvalidArgumentException("You must provide a Cabin object or a Temporary Booking ID.");
    }

    FacadesDB::beginTransaction();
    try {
      $selectedCabin = null;

      if ($temporaryBookingId) {
        $tempReservation = TemporaryReservation::find($temporaryBookingId);
        if (!$tempReservation) {
          throw new \Exception("Temporary booking ID not found.");
        }

        $cabinId = $tempReservation->cabin_id;
        $selectedCabin = Cabin::find($cabinId);
        if (!$selectedCabin) {
          throw new \Exception("Cabin not found for the given reservation ID.");
        }
      } else {
        $selectedCabin = $cabin;
      }

      if (!$selectedCabin) {
        throw new \Exception("Cabin not found.");

        $availableCabins = $this->filterCabins(
          $selectedCabin->cabin_type_id,
          $selectedCabin->cabin_category_id,
          null,
          true
        );

        if (is_array($availableCabins) && array_key_exists("error", $availableCabins)) {
          throw new \Exception($availableCabins["error"]);
        }
        $availableCabins = $availableCabins["cabins"]->toArray();
        $cabinNumberToSearch = $selectedCabin->cabin_number;
        $cabinNumbers = array_column($availableCabins, "cabin_number");
        $available = array_search($cabinNumberToSearch, $cabinNumbers) !== false;
        if (!$available) {
          throw new \Exception("Cabin not available.");
        }
      }

      $booking = new Booking();
      $booking->fill($bookingData);
      $booking->cabin_id = $selectedCabin->id;
      $booking->save();

      $passenger = null;

      if ($passengerData) {
        $passenger = $this->passengerRepository->create($passengerData, $booking);
      }

      if ($passenger && $booking) {
        // ---- start Create adjustments
        $adjustmentIds = collect($passengerData["addons"] ?? [])
          ->filter(fn($addon) => isset($addon["id"]))
          ->map(fn($addon) => $addon["id"])
          ->all();

        Log::info("Adjustments", ["ids" => $adjustmentIds]);

        $this->adjustmentsRepository->attachAdjustments($adjustmentIds, $booking);
        // ---- end Create adjustments

        if ($temporaryBookingId) {
          TemporaryReservation::find($temporaryBookingId)?->delete();
        }
        DB::commit();
        return [
          "message" => "Booking created successfully.",
          "booking" => $booking,
          "passenger" => $passenger,
        ];
      }
      Log::info($booking);
      Log::info($passenger);
      throw new \Exception("Error creating booking.");
    } catch (\Exception $e) {
      Log::error($e->getMessage());
      FacadesDB::rollBack();
      return [
        "error" => true,
        "message" => $e->getMessage(),
      ];
    }
  }
}
