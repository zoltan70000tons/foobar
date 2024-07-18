<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('/events', EventController::class);


Route::apiResource('/events', EventController::class);
Route::apiResource('/roles', RoleController::class);
Route::apiResource('/permissions', PermissionController::class);
Route::post('/permissions/addToRole', 'App\Http\Controllers\Role\RoleController@addPermissionToRole');
Route::apiResource('/organizations', OrganizationController::class);
Route::get('/organization/permissions', 'App\Http\Controllers\Permission\PermissionController@listByOrganization');
Route::get('/organization/roles', 'App\Http\Controllers\Role\RoleController@listByOrganization');
Route::get('/organization/getTeam', 'App\Http\Controllers\Api\TeamController@listMembersByOrganization');
Route::put('/organization/members/updateRole', 'App\Http\Controllers\Api\TeamController@updateMemberRoles');
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
Route::post('/register', [AuthController::class, 'register']);


Route::middleware(Authenticate::using('sanctum'))->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/verify-token', [AuthController::class, 'verifyToken']);
  Route::get('/user', [AuthController::class, 'user']);
  Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

