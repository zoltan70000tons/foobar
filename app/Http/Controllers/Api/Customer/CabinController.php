<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabin;
use App\Models\CabinType;
use App\Models\CabinCategory;
use App\Events\CabinChange;
use Illuminate\Support\Facades\Log;
use App\Enums\StatusCabin;
use Illuminate\Support\Carbon;
use App\Models\TemporaryReservation;
use App\Traits\CabinFilter;
use Illuminate\Support\Facades\DB;

class CabinController extends Controller
{
  use CabinFilter;

  /**
   * ----- SHOW CABIN -----
   *
   *
   */
  public function show($cabinTypeId, $cabinCategoryId, $cabinDeck)
  {
    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId, $cabinDeck, false);

    if (isset($filteredCabins["error"])) {
      return response()->json(["message" => $filteredCabins["error"]], $filteredCabins["status"]);
    }

    return response()->json($filteredCabins["cabins"], 200);
  }

  /***
   * Return single category by id
   *
   *
   */
  public function showCategory($categoryId)
  {
    $category = CabinCategory::where("id", $categoryId)->first();

    if (!$category) {
      return response()->json(["message" => "Category not found"], 404);
    }

    return response()->json($category);
  }

  /**
   * ----- TYPE OF CABIN -----
   *
   *
   */
  public function showTypes()
  {
    // get all cabin types
    $cabinTypes = CabinType::select("id", "cabin_type")->get();

    if (!$cabinTypes) {
      return response()->json(["message" => "No cabin types found"], 404);
    }

    return response()->json($cabinTypes);
  }

  /**
   * ----- RESERVE CABIN IN TYPE -----
   * - For selecting cabin by user
   *
   */
  public function reserveCabinInType(Request $request)
  {
    if ($request->session()->has("reserved_cabin_id")) {
      return response()->json(
        [
          "message" => "You have already reserved a cabin. Please release the current reservation to proceed.",
        ],
        403
      );
    }

    $cabinNumber = $request->input("cabin_number");
    $cabinTypeId = $request->input("cabin_type_id");
    $cabinCategoryId = $request->input("cabin_category_id");

    if (!$cabinNumber || !$cabinTypeId || !$cabinCategoryId) {
      return response()->json(
        [
          "message" => "Cabin number, type ID, and category ID are required.",
        ],
        400
      );
    }

    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);

    if (isset($filteredCabins["error"])) {
      return response()->json(["message" => $filteredCabins["error"]], $filteredCabins["status"]);
    }

    $cabin = $filteredCabins["cabins"]->firstWhere("cabin_number", (string) $cabinNumber);

    if (!$cabin) {
      return response()->json(["message" => "Cabin not found or may be reserved"], 404);
    }

    try {
      DB::beginTransaction();

      $existingReservation = TemporaryReservation::where("cabin_id", $cabin["id"])
        ->where("expires_at", ">", now())
        ->lockForUpdate()
        ->first();

      if ($existingReservation) {
        DB::rollBack();
        return response()->json(["message" => "Cabin already reserved"], 404);
      }

      $reserved = TemporaryReservation::create([
        "user_id" => $request->user()->id ?? null,
        "cabin_id" => $cabin["id"],
        "cabin_number" => $cabin["cabin_number"],
        "expires_at" => now()->addMinutes(5),
        "inventory" => 1,
      ]);

      // update temporary reservation id in session
      $request->session()->put("reserved_cabin_id", $reserved->id);

      // update session cart
      $request->session()->put("cart", [
        "cabinSelection" => "clientSelect",
        "reservationId" => $reserved->id,
        "cabin_number" => $cabin["cabin_number"],
        "cabin_category_type" => $cabin["cabin_category_type"],
        "reservationTimestamp" => now()->timestamp,
      ]);

      DB::commit();

      // update session cart

      return response()->json(
        [
          "success" => true,
          "message" => "Cabin reserved",
          "reservation_id" => $reserved->id,
        ],
        200
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(["message" => "Reservation failed"], 500);
    }
  }

  /**
   * ----- RESERVE TYPE CABIN -----
   * For selecting cabin by customer service, but we have to reserve in inventory
   *
   */
  public function reserveType(Request $request)
  {
    // -------------- TEMPORARY RESERVATION TIME --------------
    $reservation_time = (int) env("TEMPORARY_RESERVATION_TIME", 6);

    // Check if the user already has a reserved cabin in the session
    if ($request->session()->has("reserved_cabin_id")) {
      return response()->json(
        [
          "message" =>
            "You have already reserved a cabin. You can only reserve one cabin per session. To proceed, release the current reservation.",
        ],
        403
      );
    }

    $cabinTypeId = $request->input("cabin_type_id");
    $cabinCategoryId = $request->input("cabin_category_id");

    // if no cabinTypeId or cabinCategoryId is provided, return error
    if (!$cabinTypeId || !$cabinCategoryId) {
      return response()->json(["message" => "Cabin type and category are required"], 400);
    }

    // Check if the cabin is available
    $filteredCabins = $this->filterCabins($cabinTypeId, $cabinCategoryId);

    if (isset($filteredCabins["error"])) {
      return response()->json(["message" => $filteredCabins["error"]], $filteredCabins["status"]);
    }

    // if cabin number is null, get random available cabin and temporary reserve it
    $cabin = $filteredCabins["cabins"]->first();

    if (!$cabin) {
      return response()->json(["message" => "No available cabins found"], 404);
    }

    try {
      DB::beginTransaction();

      // temporary reserve the cabin
      $reserved = TemporaryReservation::create([
        "user_id" => $request->user()->id ?? null,
        "cabin_id" => $cabin["id"],
        "cabin_number" => $cabin["cabin_number"],
        "expires_at" => now()->addMinutes($reservation_time),
        "inventory" => 1,
      ]);

      // Store the reservation ID in the session to prevent further reservations
      $request->session()->put("reserved_cabin_id", $reserved->id);

      $request->session()->put("cart", [
        "choose_your_cabin" => false,
        "reservation_id" => $reserved->id,
        "reservation_timestamp" => now()->timestamp,
        "cabin_category_type" => $cabin["cabin_category_type"],
      ]);

      \Log::info("Cabin reserved", [$request->session()->get("cart")]);

      DB::commit();

      return response()->json(
        [
          "success" => true,
          "message" => "Cabin reserved",
          "reservation_id" => $reserved->id,
        ],
        200
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(["message" => "Reservation failed"], 500);
    }
  }

  /**
   * ----- RELASE THE CABIN -----
   *
   *
   */
  public function release(Request $request)
  {
    // Check if the user has a reserved cabin in the session
    if (!$request->session()->has("reserved_cabin_id")) {
      return response()->json(["message" => "No cabin reserved"], 404);
    }

    $reservationId = $request->session()->get("reserved_cabin_id");
    $reservation = TemporaryReservation::find($reservationId);

    if (!$reservation) {
      return response()->json(["message" => "No reservation found"], 404);
    }

    // Check if the reservation belongs to the authenticated user
    if ($request->user() && $reservation->user_id !== $request->user()->id) {
      return response()->json(["message" => "Unauthorized"], 403);
    }

    // Delete the reservation
    $reservation->delete();

    // Remove the reserved_cabin_id from the session
    $request->session()->forget("reserved_cabin_id");

    // update session cart
    $request->session()->forget("cart");

    return response()->json(["message" => "Cabin released"], 200);
  }
}
