<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthCustomer\CustomerRegisteredController;
use App\Http\Controllers\AuthCustomer\CustomerEmailVerificationController;
use App\Http\Controllers\AuthCustomer\CustomerLoginController;
use App\Http\Controllers\AuthCustomer\CustomerAuthController;
use App\Http\Controllers\AuthCustomer\CustomerPasswordResetController;
use App\Http\Controllers\AuthCustomer\RecoverAccountController;
use App\Http\Controllers\Api\Customer\BookingController;
use App\Http\Controllers\Api\Customer\CabinController;
use App\Http\Controllers\Api\Customer\PricingMatrixController;
use App\Http\Controllers\Api\Customer\EditProfileController;
use App\Http\Controllers\Api\Customer\AdjustmentsController;
use App\Http\Controllers\Api\Customer\EventController;
use App\Http\Controllers\Api\Customer\CartController;
use Illuminate\Routing\Router;

/**
 * Auth API Routes
 *
 * The following routes are used for customers authentication.
 *
 */

// --- PASSWORD RESET ---
Route::post("/password-email", [CustomerPasswordResetController::class, "requestReset"]);
Route::post("/password-reset", [CustomerPasswordResetController::class, "resetPassword"]);

// --- LOGIN ---
Route::post("/login-customer", [CustomerLoginController::class, "store"]);

// --- Customer recover account ---
Route::get("/recover-account", [RecoverAccountController::class, "showRecoverForm"])
  ->name("recover.account.form")
  ->middleware("signed:relative");

Route::post("/recover-account-verify", [RecoverAccountController::class, "recoverAccountVerify"])
  ->name("recover.account.verify")
  ->middleware("signed:relative");

Route::post("/recover-account-register", [RecoverAccountController::class, "recoverAccount"])
  ->name("recover.account.register")
  ->middleware("signed:relative");

// --- ADD PAX ---
Route::get("/add-pax", [BookingController::class, "addPaxVerify"])
  ->name("add.pax")
  ->middleware("signed:relative");

// --- EMAIL VERIFICATION ---
Route::post("/email/verification-notification", [CustomerEmailVerificationController::class, "store"])->middleware([
  "auth:sanctum",
  "throttle:6,1",
]);

Route::get("/email/verify/{id}/{hash}", [CustomerEmailVerificationController::class, "verify"])
  ->middleware(["signed"])
  ->name("verificationApi.verify");

// --- REGISTER ---
Route::post("/register", [CustomerRegisteredController::class, "store"]);

// --- LOGOUT ---
Route::post("/logout", [CustomerLoginController::class, "destroy"])->middleware(["auth:sanctum", "auth.customer"]);

// ---- EVENTS ----
// --- GROUP WITHOUT MIDDLEWARE ---
Route::get("/events", [EventController::class, "show"]);
// --- GROUP WITH MEMBERSHIP SALES MIDDLEWARE ---
Route::middleware(["membership_sales"])->group(function () {
  Route::get("/events/{id}", [EventController::class, "showOne"]);
});

Route::get("/events/{id}/adjustments", [AdjustmentsController::class, "show"]);
Route::get("/pricing-matrix", [PricingMatrixController::class, "index"]);
Route::get("/pricing-matrix/{cabinId}", [PricingMatrixController::class, "show"]);

// --- CART ---
Route::middleware(["one_booking_per_user"])->group(function () {
  Route::post("/cart", [CartController::class, "store"]);
  Route::put("/cart", [CartController::class, "update"]);
  Route::delete("/cart", [CartController::class, "destroy"]);
});

Route::get("/cart", [CartController::class, "check"]);
Route::get("/cart/{eventId}", [CartController::class, "index"]);

// get single category
Route::get("/cabins/category/{categoryId}", [CabinController::class, "showCategory"]);

// get cabins
Route::get("/cabins/{cabinTypeId}/{cabinCategoryId}/{cabinDeck}", [CabinController::class, "show"]);
Route::get("/cabins/types", [CabinController::class, "showTypes"]);

// This middleware will clear expired reservations from the session
Route::middleware(["clear_expired_reservation"])->group(function () {
  // reserve cabin
  Route::post("/cabin/reserve-type", [CabinController::class, "reserveType"]);
  Route::post("/cabin/reserve-cabin-in-type", [CabinController::class, "reserveCabinInType"]);
  Route::post("/cabin/release", [CabinController::class, "release"]);
});

// --- AUTH GROUP ---
Route::middleware(["auth:sanctum", "auth.customer", "verified"])->group(function () {
  Route::get("/customer", [CustomerAuthController::class, "customer"]);
  Route::post("/reset-password-inside", [CustomerAuthController::class, "update"]);

  // Profile
  Route::get("/customers/{id}", [EditProfileController::class, "getAccountIntel"])->where("id", "[0-9a-fA-F\-]{36}");
  Route::get("/customers/{id}/details", [EditProfileController::class, "getCustomerDetails"]);
  Route::post("/customers/preferred-language", [EditProfileController::class, "updatePreferredLanguage"]);
  Route::post("/customers/update-phone", [EditProfileController::class, "updatePhone"]);
  Route::post("/customers/update-email", [EditProfileController::class, "updateEmail"]);
  Route::post("/customers/update-password", [EditProfileController::class, "updatePassword"]);

  // Booking
  // --- booking init
  Route::post("/booking-init", [BookingController::class, "store"]);
  // --- all bookings
  Route::get("/my-bookings", [BookingController::class, "allBookings"]);
  // --- single booking
  Route::get("/my-bookings/{bookingCode}", [BookingController::class, "singleBooking"]);
  // ! ! ! ! ! --- delete booking THIS ROUTE SHOULD BE DELETED ON PROD! ! ! ! ! ! ! ! !
  //  Route::delete("/my-bookings/{id}", [BookingController::class, "destroy"]);

  // set slot empty
  Route::post("/my-bookings/{bookingCode}/set-empty-seat", [BookingController::class, "emptySeat"]);
  // add passenger manually
  Route::post("/my-bookings/{bookingCode}/add-passenger", [BookingController::class, "addPassenger"]);
  // add passenger via email
  Route::post("/my-bookings/{bookingCode}/add-passenger-via-email", [BookingController::class, "addPassengerViaEmail"]);
});

// --- TEST PURPOSE FOR BROADCASTING ---
// Route::get('/cabins', [CabinController::class, 'show']);
// Route::get('/trigger-cabins', [CabinController::class, 'trigger']);
