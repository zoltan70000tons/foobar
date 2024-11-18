<?php

use Illuminate\Support\Facades\Route;

// AUTH CUSTOMER
use App\Http\Controllers\AuthCustomer\CustomerRegisteredController;
//use App\Http\Controllers\AuthCustomer\CustomerPassResetInsideController;
use App\Http\Controllers\AuthCustomer\CustomerEmailVerificationController;
use App\Http\Controllers\AuthCustomer\CustomerLoginController;
use App\Http\Controllers\AuthCustomer\CustomerAuthController;
use App\Http\Controllers\AuthCustomer\CustomerPasswordResetController;
use App\Http\Controllers\AuthCustomer\RecoverAccountController;

// BOOKING
use App\Http\Controllers\Api\Customer\BookingController;

// CABIN
use App\Http\Controllers\Api\Customer\CabinController;

// PRICING MATRIX
use App\Http\Controllers\Api\Customer\PricingMatrixController;

// EDIT PROFILE
use App\Http\Controllers\Api\Customer\EditProfileController;

// BROADCAST
use App\Events\CabinChange;

// log
use Illuminate\Support\Facades\Log;

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

// --- CUSTOMER MIDDLEWARE AFTER LOGIN ---
Route::middleware(["auth:sanctum", "verified"])->group(function () {
  Route::get("/customer", [CustomerAuthController::class, "customer"]);
  Route::post("/reset-password-inside", [CustomerAuthController::class, "update"]);
});

// --- GROUP WITH MEMBERSHIP SALES MIDDLEWARE ---
Route::middleware(["membership_sales"])->group(function () {
  Route::get("/booking-init", [BookingController::class, "store"]);
  Route::get("/events/{id}", [BookingController::class, "showOne"]);
});

// --- GROUP WITHOUT MIDDLEWARE ---
Route::get("/events", [BookingController::class, "show"]);
//Route::get('/events/{id}/{language?}', [BookingController::class, 'showOne']);

Route::get("/pricing-matrix", [PricingMatrixController::class, "index"]);
Route::get("/pricing-matrix/{cabinId}", [PricingMatrixController::class, "show"]);

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

Route::middleware(["auth:sanctum", "auth.customer", "verified"])->group(function () {
  Route::get("/customers/{id}", [EditProfileController::class, "getAccountIntel"])->where("id", "[0-9a-fA-F\-]{36}");
  Route::get("/customers/{id}/details", [EditProfileController::class, "getCustomerDetails"]);
  Route::post("/customers/preferred-language", [EditProfileController::class, "updatePreferredLanguage"]);
  Route::post("/customers/update-phone", [EditProfileController::class, "updatePhone"]);
  Route::post("/customers/update-email", [EditProfileController::class, "updateEmail"]);
  Route::post("/customers/update-password", [EditProfileController::class, "updatePassword"]);
});

// --- TEST PURPOSE FOR BROADCASTING ---
// Route::get('/cabins', [CabinController::class, 'show']);
// Route::get('/trigger-cabins', [CabinController::class, 'trigger']);
