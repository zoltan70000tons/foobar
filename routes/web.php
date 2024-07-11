<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/{slug}/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/{slug}/invitations', [InvitationController::class, 'store'])->name('invitations.store');
});

Route::get('/create-organization', function () {
    return Inertia::render('RegisterOrganization');
})->name('organization.register');
Route::get('/{slug}/join-organization', function () {
    return Inertia::render('JoinOrganization');
})->name('organization.join');
Route::get('/{slug}/team', function () {
    return Inertia::render('Teams');
})->middleware(['auth'])->name('teams');
Route::get('/{slug}/team/roles', function () {
    return Inertia::render('ManageRole');
})->middleware(['auth'])->name('roles');
Route::get('/{slug}/team/permissions', function () {
    return Inertia::render('ManagePermission');
})->middleware(['auth'])->name('permissions');



require __DIR__.'/auth.php';
