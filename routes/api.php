<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/events', EventController::class);


/**
 * Auth API Routes
 * 
 * The following routes are used for authentication.
 * 
 * These routes have prefix of /api.
 * /api/login - POST - Login a user
 * /api/logout - POST - Logout a user
 * etc.
 * 
 * 
 * !!!!! TO DO !!!!!
 * Create the group of routes for the auth routes.
 * There we will check if user role is user if yes,
 * We want to prevent the user from accessing the ADMIN routes.
 * So only /api routes should be accesible for role USER
 */
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);