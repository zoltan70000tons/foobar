<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;


Route::middleware(['allowed_domains'])->group(function () {
    Route::get('/payment', [NotificationController::class, 'sendPaymentEmail']);
    Route::get('/confirmation', [NotificationController::class, 'sendConfirmationEmail']);
});