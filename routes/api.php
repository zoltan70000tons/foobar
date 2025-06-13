<?php

use Illuminate\Support\Facades\Route;

// controllers
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
use App\Http\Controllers\Api\Customer\InvitationController;
use App\Http\Controllers\Api\Customer\CheckBookingController;
use App\Http\Controllers\NotificationController;

// middleware
use App\Http\Middleware\ApiRedirectHttp;
use App\Http\Middleware\EnsureUserIsNotCustomer;

// --- PASSWORD RESET ---
Route::post('/password-email', [CustomerPasswordResetController::class, 'requestReset'])->middleware(['throttle:10,1', 'guest']);
Route::post('/password-reset', [CustomerPasswordResetController::class, 'resetPassword'])->middleware(['throttle:10,1', 'guest']);

// --- LOGIN ---
Route::post('/login-customer', [CustomerLoginController::class, 'store'])->middleware(['throttle:20,1', 'verified']);

// --- EMAIL VERIFICATION ---
Route::post('/email/verification-notification', [CustomerEmailVerificationController::class, 'reSend'])->middleware([
  'auth:sanctum',
  'throttle:10,1',
]);

Route::get('/email/verify/{id}/{hash}', [CustomerEmailVerificationController::class, 'verify'])
  ->middleware(['web', 'signed'])
  ->withoutMiddleware([ApiRedirectHttp::class, EnsureUserIsNotCustomer::class])
  ->name('verificationApi.verify');

// --- REGISTER ---
Route::post('/register', [CustomerRegisteredController::class, 'store'])->middleware('throttle:10,1');
Route::post('/activate-survivor-account', [CustomerRegisteredController::class, 'storeUserSurvivor'])->middleware(
  'throttle:10,1'
);

// --- LOGOUT ---
Route::post('/logout', [CustomerLoginController::class, 'destroy'])->middleware(['auth:sanctum']);

// ---- EVENTS ----
Route::get('/events', [EventController::class, 'show']);

// ---- ADJUSTMENTS ----
Route::get('/events/{id}/adjustments', [AdjustmentsController::class, 'show']);

// ---- PRICING MATRIX ----
Route::get('/pricing-matrix/{eventId}/{cabinTypeId}', [PricingMatrixController::class, 'show']);

// ---- CART PER EVENT ----
Route::get('/cart/{eventId}', [CartController::class, 'index']);

// --- ADD PAX validate page with form
Route::get('/add-pax', [AddPaxController::class, 'validate'])
  ->name('add.pax')
  ->middleware('signed:relative');

// --- ADD PAX - NON AUTH - store Booking
Route::post('/add-pax/{eventId}/{bookingCode}/{token}', [AddPaxController::class, 'store'])->middleware([
  'throttle:10,1',
]);

// --- CHECK BOOKING ---
Route::post('/check-booking-login', [CheckBookingController::class, 'login'])->middleware(['throttle:15,1']);

Route::middleware(['auth:passenger'])->group(function () {
  Route::get('/check-booking', [CheckBookingController::class, 'getBooking']);
  Route::post('/check-booking-logout', [CheckBookingController::class, 'logout']);
});

// // --- CART ---
// Route::middleware(['throttle:40,1', 'one_booking_per_user'])->group(function () {
//   Route::post('/cart', [CartController::class, 'store']);
//   Route::put('/cart', [CartController::class, 'update']);
//   Route::delete('/cart', [CartController::class, 'destroy']);
// });

// --- GET CABINS ---
Route::get('/cabins/types', [CabinController::class, 'showTypes']);

// --- GET SINGLE CATEGORY ---
Route::get('/cabins/category/{categoryId}', [CabinController::class, 'showCategory']);

// --- GROUP WITH MEMBERSHIP SALES MIDDLEWARE ---
// Route::middleware(['membership_sales'])->group(function () {
//   Route::get('/events/{id}', [EventController::class, 'showOne']);
// });

Route::get('/events/{id}', [EventController::class, 'showOne']);

Route::middleware([
  'auth:sanctum',
  'verified',
  'booking_status',
  'one_booking_per_user',
  'clear_expired_reservation',
])->group(function () {
  // --- CART (with membership_sales) ---
  Route::middleware(['membership_sales'])->group(function () {
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart', [CartController::class, 'update']);
    Route::delete('/cart', [CartController::class, 'destroy']);

    // reserve cabin
    Route::post('/cabin/reserve-type', [CabinController::class, 'reserveType']);
    Route::post('/cabin/reserve-cabin-in-type', [CabinController::class, 'reserveCabinInType']);
    Route::post('/cabin/release', [CabinController::class, 'release']);

    // --- booking init
    Route::post('/booking-init', [BookingController::class, 'store']);
  });

  // Route::get('/cabins/{cabinTypeId}/{cabinCategoryCode}/{cabinDeck}', [CabinController::class, 'show']);
  Route::get('/cabins/{cabinTypeId}/{cabinCategoryCode}/{cabinCapacity}/{cabinDeck}', [CabinController::class, 'show']);

  // reserve cabin
  // Route::post('/cabin/reserve-type', [CabinController::class, 'reserveType']);
  // Route::post('/cabin/reserve-cabin-in-type', [CabinController::class, 'reserveCabinInType']);
  // Route::post('/cabin/release', [CabinController::class, 'release']);


  Route::get('/customer', [CustomerAuthController::class, 'customer']);
  Route::post('/reset-password-inside', [CustomerAuthController::class, 'update']);
  Route::put('/update-profile', [CustomerAuthController::class, 'updateProfile']);
  Route::put('/update-email', [CustomerAuthController::class, 'updateEmail']);
  Route::get('/customer/can-delete-account', [CustomerAuthController::class, 'canDeleteAccount']);
  Route::post('/delete-account', [CustomerAuthController::class, 'deleteAccount']);

  // set slot empty
  Route::post('/my-bookings/{eventId}/{bookingCode}/set-empty-seat', [BookingController::class, 'emptySeat']);
  Route::put('/my-bookings/{eventId}/{bookingCode}/remove-empty-seat', [BookingController::class, 'removeEmptySeat']);

  // reset passenger seat
  Route::put('/my-bookings/{eventId}/{bookingCode}/reset-passenger-seat', [
    BookingController::class,
    'resetPassengerSeat',
  ]);

  // add passenger manually
  Route::post('/my-bookings/{eventId}/{bookingCode}/add-passenger', [BookingController::class, 'addPassenger']);
  // add passenger via email
  Route::post('/my-bookings/{eventId}/{bookingCode}/add-passenger-via-email', [
    BookingController::class,
    'addPassengerViaEmail',
  ]);
  // cancel invitation
  Route::post('/my-bookings/{eventId}/{bookingCode}/cancel-invitation', [BookingController::class, 'cancelInvitation']);

  // --- all bookings
  Route::get('/my-bookings', [BookingController::class, 'allBookings']);

  // --- single booking
  Route::get('/my-bookings/{eventId}/{bookingCode}', [BookingController::class, 'singleBooking']);
  Route::get('/my-bookings/{eventId}/request-id/{requestId}', [BookingController::class, 'singleBookingByRequestId']);

  // --- single invitation
  Route::get('/my-bookings/{eventId}/{bookingCode}/invitation/{token}', [InvitationController::class, 'index']);
  // --- single invitation add pax
  Route::post('/my-bookings/{eventId}/{bookingCode}/invitation/{token}/add-pax', [
    InvitationController::class,
    'addPax',
  ]);
  // --- single invitation remove invitation
  Route::post('/my-bookings/{eventId}/{bookingCode}/invitation/{token}/cancel', [
    InvitationController::class,
    'removeInvitation',
  ]);
});
