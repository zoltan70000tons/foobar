<?php

use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Middleware\TeamsPermission;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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
    
    Route::prefix('{slug}')->group(function () {
        Route::get('/team', function () {
            return Inertia::render('Teams');
        })->name('teams');
        
        Route::get('/team/roles', function () {
            return Inertia::render('ManageRole');
        })->name('roles');
        
        Route::get('/team/permissions', function () {
            return Inertia::render('ManagePermission');
        })->name('permissions');
    });

    Route::apiResource('/events', EventController::class);
    Route::apiResource('/roles', RoleController::class);
    Route::apiResource('/permissions', PermissionController::class);
    Route::post('/permissions/addToRole', 'App\Http\Controllers\Role\RoleController@addPermissionToRole');
    Route::apiResource('/organizations', OrganizationController::class);
    Route::get('/organization/permissions', 'App\Http\Controllers\Permission\PermissionController@listByOrganization');
    Route::get('/organization/roles', 'App\Http\Controllers\Role\RoleController@listByOrganization');
    Route::get('/organization/getTeam', 'App\Http\Controllers\Api\TeamController@listMembersByOrganization');
    Route::put('/organization/members/updateRole', 'App\Http\Controllers\Api\TeamController@updateMemberRoles');
    Route::get('/users/getPermissions', 'App\Http\Controllers\Role\RoleController@getPermissions');
    Route::post('/team/send-invitations', 'App\Http\Controllers\InvitationController@store');
    Route::post('/member/update', 'App\Http\Controllers\Api\TeamController@updateMember')->name('member.update');


});


Route::get('/join-organization', [OrganizationController::class, 'join'])->name('organization.join');
Route::put('/join-organization', [OrganizationController::class, 'join'])->name('organization.join');

// Route::get('/register-organization', function () {
//     return Inertia::render('RegisterOrganization');
// })->name('organization.register');


//Route::get('/send-test-email', [MailTestController::class, 'sendMail']);




require __DIR__.'/auth.php';
