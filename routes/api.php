<?php


use Illuminate\Support\Facades\Route;


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
//Route::post('/login', [AuthenticatedSessionController::class, 'store']);
//Route::post('/register', [RegisteredUserController::class, 'store']);


// group of routes for the auth routes.
Route::middleware(['auth:sanctum'])->group(function () {});
