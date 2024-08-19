<?php

use Illuminate\Support\Facades\Route;

// AUTH CUSTOMER
use App\Http\Controllers\AuthCustomer\CustomerRegisteredController;
use App\Http\Controllers\AuthCustomer\CustomerPassResetInsideController;
use App\Http\Controllers\AuthCustomer\CustomerEmailVerificationController;
use App\Http\Controllers\AuthCustomer\CustomerLoginController;
use App\Http\Controllers\AuthCustomer\CustomerAuthController;
use App\Http\Controllers\AuthCustomer\CustomerPasswordResetController;

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
Route::post('/reset-password-data', [CustomerPasswordResetController::class, 'resetPassword']);

Route::get('/password-reset/{id}/{token}', [CustomerPasswordResetController::class, 'verifyToken'])
  ->middleware(['signed'])
  ->name('passwordApi.reset');

// --- LOGIN ---
Route::post('/login-customer', [CustomerLoginController::class, 'store'])->middleware('guest');

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
