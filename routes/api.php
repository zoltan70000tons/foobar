<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Permission\PermissionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('/events', EventController::class);
Route::apiResource('/roles', RoleController::class);
Route::apiResource('/permissions', PermissionController::class);
Route::post('/permissions/addToRole', 'App\Http\Controllers\Role\RoleController@addPermissionToRole');
Route::apiResource('/organizations', OrganizationController::class);