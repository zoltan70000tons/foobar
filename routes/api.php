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
use App\Http\Controllers\AuthCustomer\AuthenticatedSessionController;
use App\Http\Controllers\AuthCustomer\RegisteredUserController;
use App\Http\Controllers\AuthCustomer\PasswordController;

use App\Http\Controllers\AuthTokenController;

Route::post('/sanctum/token', [AuthTokenController::class, 'issueToken']);


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
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);


// group of routes for the auth routes.
Route::middleware(['auth:sanctum'])->group(function () {
  Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
  Route::get('/user', [AuthController::class, 'user']);
  Route::post('/reset-password-inside', [PasswordController::class, 'update']);
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

});
