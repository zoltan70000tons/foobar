<?php

use Illuminate\Support\Facades\Route;

// AUTH CUSTOMER
use App\Http\Controllers\AuthCustomer\CustomerRegisteredController;
use App\Http\Controllers\AuthCustomer\CustomerPassResetInsideController;
use App\Http\Controllers\AuthCustomer\CustomerEmailVerificationController;
use App\Http\Controllers\AuthCustomer\CustomerLoginController;
use App\Http\Controllers\AuthCustomer\CustomerAuthController;
use App\Http\Controllers\AuthCustomer\CustomerPasswordResetController;

// BOOKING
use App\Http\Controllers\Api\Customer\BookingController;

// CABIN
use App\Http\Controllers\Api\Customer\CabinController;

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
Route::post('/reset-password', [CustomerPasswordResetController::class, 'requestReset']);

Route::get('/password-reset/{id}/verify', [CustomerPasswordResetController::class, 'verifyResetLink'])
  ->middleware(['signed'])
  ->name('passwordApi.verify');

Route::post('/password-reset/{id}', [CustomerPasswordResetController::class, 'resetPassword']);

// --- LOGIN ---
Route::post('/login-customer', [CustomerLoginController::class, 'store'])->middleware('guest:customer');

// --- EMAIL VERIFICATION ---  
Route::post('/email/verification-notification', [CustomerEmailVerificationController::class, 'store'])
  ->middleware(['auth:sanctum', 'throttle:6,1']);

Route::get('/email/verify/{id}/{hash}', [CustomerEmailVerificationController::class, 'verify'])
  ->middleware(['signed'])
  ->name('verificationApi.verify');

// --- REGISTER ---
Route::post('/register', [CustomerRegisteredController::class, 'store']);

// --- LOGOUT ---
Route::post('/logout', [CustomerLoginController::class, 'destroy'])
  ->middleware(['auth:sanctum', 'auth.customer']);

// --- CUSTOMER MIDDLEWARE AFTER LOGIN ---
Route::middleware(['auth:sanctum', 'auth.customer', 'verified'])->group(function () {
  Route::get('/customer', [CustomerAuthController::class, 'customer']);
  Route::post('/reset-password-inside', [CustomerPassResetInsideController::class, 'update']);
});

// --- GROUP WITH MEMBERSHIP SALES MIDDLEWARE ---
Route::middleware(['membership_sales'])->group(function () {
  Route::get('/booking-init', [BookingController::class, 'store']);
  Route::get('/events/{id}', [BookingController::class, 'showOne']);
});

// --- GROUP WITHOUT MIDDLEWARE ---
Route::get('/events', [BookingController::class, 'show']);

// Route::get('/cabins', function () {
//   // test broadcast
//   broadcast(new CabinChange());
// });

Route::get('/cabins', [CabinController::class, 'show']);
Route::get('/trigger-cabins', [CabinController::class, 'trigger']);
