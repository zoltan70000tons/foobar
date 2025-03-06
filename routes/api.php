<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthCustomer\CustomerRegisteredController;
use App\Http\Controllers\AuthCustomer\CustomerEmailVerificationController;
use App\Http\Controllers\AuthCustomer\CustomerLoginController;
use App\Http\Controllers\AuthCustomer\CustomerAuthController;
use App\Http\Controllers\AuthCustomer\CustomerPasswordResetController;
use App\Http\Controllers\Api\Customer\BookingController;
use App\Http\Controllers\Api\Customer\CabinController;
use App\Http\Controllers\Api\Customer\PricingMatrixController;
use App\Http\Controllers\Api\Customer\AdjustmentsController;
use App\Http\Controllers\Api\Customer\EventController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\AddPaxController;

// middleware
use App\Http\Middleware\ApiRedirectHttp;
use App\Http\Middleware\EnsureUserIsNotCustomer;

/**
 * Auth API Routes
 *
 * The following routes are used for customers authentication.
 *
 */

// --- PASSWORD RESET ---
Route::post('/password-email', [CustomerPasswordResetController::class, 'requestReset'])->middleware('throttle:6,1');
Route::post('/password-reset', [CustomerPasswordResetController::class, 'resetPassword'])->middleware('throttle:6,1');

// --- LOGIN ---
Route::post('/login-customer', [CustomerLoginController::class, 'store'])->middleware('throttle:10,1');

// --- EMAIL VERIFICATION ---
Route::post('/email/verification-notification', [CustomerEmailVerificationController::class, 'reSend'])->middleware([
  'auth:sanctum',
  'throttle:6,1',
]);

Route::get('/email/verify/{id}/{hash}', [CustomerEmailVerificationController::class, 'verify'])
  ->middleware(['web', 'signed'])
  ->withoutMiddleware([ApiRedirectHttp::class, EnsureUserIsNotCustomer::class])
  ->name('verificationApi.verify');

// --- REGISTER ---
Route::post('/register', [CustomerRegisteredController::class, 'store'])->middleware('throttle:6,1');
Route::post('/activate-survivor-account', [CustomerRegisteredController::class, 'storeUserSurvivor'])->middleware(
  'throttle:6,1'
);

// --- LOGOUT ---
Route::post('/logout', [CustomerLoginController::class, 'destroy'])->middleware(['auth:sanctum', 'auth.customer']);

// ---- EVENTS ----
Route::get('/events', [EventController::class, 'show']);
// --- GROUP WITH MEMBERSHIP SALES MIDDLEWARE ---
Route::middleware(['membership_sales'])->group(function () {
  Route::get('/events/{id}', [EventController::class, 'showOne']);
});

Route::get('/events/{id}/adjustments', [AdjustmentsController::class, 'show']);
//Route::get('/pricing-matrix', [PricingMatrixController::class, 'index']);
Route::get('/pricing-matrix/{eventId}/{cabinTypeId}', [PricingMatrixController::class, 'show']);

Route::get('/cart/{eventId}', [CartController::class, 'index']);

// --- CART ---
Route::middleware(['throttle:40,1', 'one_booking_per_user'])->group(function () {
  Route::post('/cart', [CartController::class, 'store']);
  Route::put('/cart', [CartController::class, 'update']);
  Route::delete('/cart', [CartController::class, 'destroy']);
});

// get cabins
Route::get('/cabins/types', [CabinController::class, 'showTypes']);
// get single category
Route::get('/cabins/category/{categoryId}', [CabinController::class, 'showCategory']);

Route::middleware(['auth:sanctum', 'auth.customer', 'verified', 'booking_status', 'clear_expired_reservation'])->group(
  function () {
    // Route::get('/cabins/{cabinTypeId}/{cabinCategoryCode}/{cabinDeck}', [CabinController::class, 'show']);
    Route::get('/cabins/{cabinTypeId}/{cabinCategoryCode}/{cabinCapacity}/{cabinDeck}', [
      CabinController::class,
      'show',
    ]);

    // reserve cabin
    Route::post('/cabin/reserve-type', [CabinController::class, 'reserveType']);
    Route::post('/cabin/reserve-cabin-in-type', [CabinController::class, 'reserveCabinInType']);
    Route::post('/cabin/release', [CabinController::class, 'release']);

    Route::get('/customer', [CustomerAuthController::class, 'customer']);
    Route::post('/reset-password-inside', [CustomerAuthController::class, 'update']);
    Route::put('/update-profile', [CustomerAuthController::class, 'updateProfile']);
    Route::put('/update-email', [CustomerAuthController::class, 'updateEmail']);
    Route::post('/delete-account', [CustomerAuthController::class, 'deleteAccount']);

    // set slot empty
    Route::post('/my-bookings/{bookingCode}/set-empty-seat', [BookingController::class, 'emptySeat']);
    // add passenger manually
    Route::post('/my-bookings/{bookingCode}/add-passenger', [BookingController::class, 'addPassenger']);
    // add passenger via email
    Route::post('/my-bookings/{bookingCode}/add-passenger-via-email', [
      BookingController::class,
      'addPassengerViaEmail',
    ]);
    // cancel invitation
    Route::post('/my-bookings/{bookingCode}/cancel-invitation', [BookingController::class, 'cancelInvitation']);

    // Booking
    // --- booking init
    Route::post('/booking-init', [BookingController::class, 'store']);
    // --- all bookings
    Route::get('/my-bookings', [BookingController::class, 'allBookings']);
    // --- single booking
    Route::get('/my-bookings/{bookingCode}', [BookingController::class, 'singleBooking']);
  }
);

// Booking confirmation
//Route::get('/booking-confirmation/{bookingCode}', [BookingController::class, 'bookingConfirmation']);

// Add pax
// --- add pax form
Route::get('/add-pax', [BookingController::class, 'validateAddPassenger'])
  ->name('add.pax')
  ->middleware('signed:relative');

// --- ADD PAX Booking
Route::post('/add-pax/{bookingCode}/{token}', [BookingController::class, 'submitAddPassenger'])->middleware([
  'throttle:10,1',
]);

// --- ADD PAX PROFILE
Route::post('/check-booking', [AddPaxController::class, 'show'])->middleware(['throttle:15,1']);
