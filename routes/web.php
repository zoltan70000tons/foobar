<?php

use App\Http\Controllers\Api\Customer\PricingMatrixController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\CabinCategoriesController;
use App\Http\Controllers\CabinsController;
use App\Http\Controllers\DeletedController;
use App\Http\Controllers\CustomerTagController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotAllowedController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\TagsController;
use App\Http\Middleware\TeamsPermission;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Events\TestMessageSent;
use App\Http\Controllers\AdjustmentsController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardCreditController;
use App\Http\Controllers\TemporaryPasswordController;
use Laravel\Passport\Passport;
use App\Http\Controllers\OAuth\PublicAuthController;
use App\Http\Controllers\OAuth\VerifyEmailController;


Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
  ]);
})->middleware(['auth', 'verified', 'electron_auth']);

Route::get('/token', function () {
  return csrf_token();
});

Route::post('/contact/submit', [ContactFormController::class, 'submit']);

Route::get('/dashboard', function () {
  return Inertia::render('Dashboard');
})
  ->middleware(['auth', 'verified', 'electron_auth'])
  ->name('dashboard');

Route::middleware(['auth', 'electron_auth'])->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::get('/{slug}/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
  Route::post('/{slug}/invitations', [InvitationController::class, 'store'])->name('invitations.store');

  Route::get('/team', function () {
    return Inertia::render('Teams');
  })->name('teams');

  Route::get('/team/roles', function () {
    return Inertia::render('ManageRole');
  })->name('roles');

  Route::get('/team/permissions', function () {
    return Inertia::render('ManagePermission');
  })->name('permissions');

  Route::apiResource('/roles', RoleController::class);
  Route::apiResource('/permissions', PermissionController::class);
  Route::post('/permissions/addToRole', 'App\Http\Controllers\Role\RoleController@addPermissionToRole');
  Route::apiResource('/organizations', OrganizationController::class);
  Route::get('/organization/permissions', 'App\Http\Controllers\Permission\PermissionController@listByOrganization');
  Route::get('/organization/roles', 'App\Http\Controllers\Role\RoleController@listByOrganization');
  Route::get('/organization/getTeam', 'App\Http\Controllers\Api\TeamController@listMembersByOrganization')->name('team.members.list');
  Route::put('/organization/members/updateRole', 'App\Http\Controllers\Api\TeamController@updateMemberRoles')->name(
    'member.updateRole'
  );
  Route::get('/users/getPermissions', 'App\Http\Controllers\Role\RoleController@getPermissions');
  Route::post('/team/send-invitations', 'App\Http\Controllers\InvitationController@store');
  Route::post('/member/update', 'App\Http\Controllers\Api\TeamController@updateMember')->name('member.update');

  //There is a conflict between create and show routes,
  //so to make it work correctly, it should be overridden before defining the Route::resource
  //Events crud
  Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
  Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
  Route::get('/events', [EventController::class, 'index'])->name('events.index');
  Route::resource('/events', EventController::class);

  Route::get('/events/{id}/cabins', [CabinsController::class, 'index'])->name('cabins.index');
  Route::get('/events/{id}/cabins/categories', [CabinCategoriesController::class, 'index'])->name('cabins.categories');
  Route::get('/events/{id}/cabins/tags', [TagsController::class, 'index'])->name('cabins.tags');
  Route::get('/events/{id}/cabins/create', [CabinsController::class, 'create'])->name('cabins.create');
  Route::post('/events/{id}/cabins/store', [CabinsController::class, 'store'])->name('cabins.store');
  Route::post('/events/{id}/cabins/createShared', [CabinsController::class, 'createShared'])->name('cabins.createShared');
  Route::get('/events/{id}/cabins/getData', [CabinsController::class, 'getData'])->name('cabins.getData');
  
  //Cabin categories
  Route::get('/events/{id}/cabins/categories/{catId}/show', [CabinCategoriesController::class, 'show'])->name(
    'cabinCategory.show'
  );
  Route::get('/events/{id}/cabins/categories/create', [CabinCategoriesController::class, 'create'])->name(
    'cabinCategory.create'
  );
  Route::post('/events/{id}/cabins/categories/store', [CabinCategoriesController::class, 'store'])->name(
    'cabinCategory.store'
  );
  Route::get('/events/{id}/cabins/categories/{catId}/edit', [CabinCategoriesController::class, 'edit'])->name(
    'cabinCategory.edit'
  );
  Route::post('/events/{id}/cabins/categories/{catId}/update', [CabinCategoriesController::class, 'update'])->name(
    'cabinCategory.update'
  );
  Route::delete('/events/{id}/cabins/categories/{catId}/delete', [CabinCategoriesController::class, 'destroy'])->name(
    'cabinCategory.destroy'
  );

  //Cabins Add Tag
  Route::post('/events/{id}/cabins/addTag', [CabinsController::class, 'addTag'])->name('cabins.addTag');
  Route::post('/events/{id}/cabins/updateStatus', [CabinsController::class, 'updateStatus'])->name(
    'cabins.updateStatus'
  );

  //Cabin edit
  Route::get('/events/{id}/cabins/{cabin_id}/edit', [CabinsController::class, 'edit'])->name('cabins.edit');
  Route::post('/events/{id}/cabins/{cabin_id}/update', [CabinsController::class, 'update'])->name('cabins.update');

  Route::post('/events/{id}/bookings/createManual', [BookingsController::class, 'store'])->name(
    'bookings.createManual'
  );
  //Booking controller
  // Route::match(['GET', 'POST'], '/events/{id}/bookings', [BookingsController::class, 'index'])
  // ->where('id', '[0-9]+|all')
  // ->name('bookings.index');
  // --- @JG - change to get only, prevent duplication
  Route::get('/events/{id}/bookings', [BookingsController::class, 'index'])
    ->where('id', '[0-9]+|all')
    ->name('bookings.index');

  Route::post('/events/{id}/bookings/update-cabin', [BookingsController::class, 'cabinUpdate'])->name(
    'bookings.updateCabin'
  );
    Route::post('/events/{id}/bookings/upgrade-cabin', [BookingsController::class, 'cabinUpgrade'])->name(
        'bookings.upgradeCabin'
    );
  Route::post('/events/{id}/bookings/update-code', [BookingsController::class, 'codeUpdate'])->name(
    'bookings.updateCode'
  );
  Route::post('/events/{id}/bookings/update-status', [BookingsController::class, 'statusUpdate'])->name(
    'bookings.updateStatus'
  );
  Route::post('/events/{id}/bookings/add-comment', [BookingsController::class, 'addComment'])->name(
    'bookings.addComment'
  );
  Route::post('/events/{id}/bookings/update-tags', [BookingsController::class, 'updateTags'])->name(
    'bookings.updateTags'
  );
  Route::get('/events/{id}/bookings/{booking_code}', [BookingsController::class, 'show'])->name('bookings.show');
  Route::post('/events/{id}/bookings/{booking_code}', [BookingsController::class, 'update'])->name('bookings.update');
  Route::put('/bookings/{booking}/assign-agent', [BookingsController::class, 'assignAgent'])->name(
    'bookings.assignAgent'
  );
  Route::get('/bookings/edit-mode', [BookingsController::class, 'editMode'])->name('bookings.editMode');
  Route::get('/bookings/edit-mode-reassign', [BookingsController::class, 'reAssign'])->name('bookings.reAssign');

  Route::post('/events/{id}//bookings/cancel', [BookingsController::class, 'cancel'])->name('bookings.cancel');
  Route::get('/cabins/available', [BookingsController::class, 'getAvailableCabins'])->name('cabins.available');
  Route::get('/cabins/upgrade-list', [BookingsController::class, 'getCabinsToUpgradeTo'])->name('cabins.upgrade-list');

  Route::get('/customers/paginated', [CustomerController::class, 'getPaginated'])->name('customers.paginated');
  Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
  Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
  Route::get('/customers/{user}', [CustomerController::class, 'show'])
    ->where('user', '[a-f0-9\-]+')
    ->name('customers.show');
  Route::get('/customers/{user}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
  Route::put('/customers/{user}/update', [CustomerController::class, 'update'])->name('customers.update');
  Route::delete('/customers/{user}', [CustomerController::class, 'destroy'])->name('customers.destroy');
  Route::get('/customers/by-survivor-number/{survivorNumber}', [CustomerController::class, 'editBySurvivorNumber'])
      ->where('survivorNumber', '[0-9]{9}')
      ->name('customers.editBySurvivorNumber');
  Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
  //Route::resource('customers', CustomerController::class);
    Route::post('/customers/{user}/add-comment', [CustomerController::class, 'addComment'])->name(
        'customers.addComment'
    );
    Route::post('/customers/{user}/update-tags', [CustomerController::class, 'updateTags'])->name(
        'customers.updateTags'
    );
    Route::post('/customers/{user}/delete-comment', [CustomerController::class, 'deleteComment'])->name(
        'customers.delete-comment'
    );
  
  // cutsomer temporary password
  Route::post('/customers/{user}/temporary-password', [TemporaryPasswordController::class, 'create'])->name(
    'customers.temporaryPassword.create'
  );

  // delete temporary password
  Route::delete('/customers/{user}/temporary-password', [TemporaryPasswordController::class, 'delete'])->name(
    'customers.temporaryPassword.delete'
  );

  Route::get('/not-allowed', [NotAllowedController::class, 'index'])->name('access.denied');
  Route::get('/menu/bookings', [MenuController::class, 'getEvents'])->name('menu.bookings');
  Route::get('/passengers/search', [PassengerController::class, 'search'])->name('seat.search');

  Route::prefix('/events/{id}/booking/{booking_id}/passengers')->group(function () {
    Route::post('/update/seat', [PassengerController::class, 'updateSeat'])->name('seat.update');
    Route::post('/empty/seat', [PassengerController::class, 'emptySeat'])->name('seat.empty');
    Route::post('/release/seat', [PassengerController::class, 'releaseSeat'])->name('seat.release');
    Route::post('/cancel/invitation', [PassengerController::class, 'cancelPassengerInvitation'])->name(
      'passenger_invitation.cancel'
    );
  });

  Route::get('/not-allowed', [NotAllowedController::class, 'index'])->name('access.denied');
  Route::get('/menu/bookings', [MenuController::class, 'getEvents'])->name('menu.bookings');

  Route::prefix('passengers')->group(function () {
    Route::post('/add', [PassengerController::class, 'addPassenger']);
    Route::post('/edit/{id}', [PassengerController::class, 'editPassenger']);
    Route::get('/validate', [PassengerController::class, 'validatePassenger']);
    Route::get('/search', [PassengerController::class, 'search']);
  });

  Route::prefix('adjustments')->group(function () {
    Route::post('{event_id}/{booking_id}/add-to-booking', [AdjustmentsController::class, 'createAdjustment'])->name(
      'bookings.createAdjustment'
    );
    Route::post('{event_id}/{booking_id}/delete', [AdjustmentsController::class, 'deleteAdjustment'])->name(
      'bookings.deleteAdjustment'
    );
    Route::post('{event_id}/{booking_id}/update', [AdjustmentsController::class, 'updateAdjustment'])->name(
      'bookings.updateAdjustment'
    );
  });

  Route::prefix('payments')->group(function () {
    Route::post('{event_id}/{booking_id}/store', [PaymentController::class, 'store'])->name('manual.payment');
    Route::post('{event_id}/{booking_id}/payment/delete', [PaymentController::class, 'delete'])->name(
      'payments.delete'
    );
  });

  Route::prefix('fees')->group(function () {
    Route::post('{event_id}/{booking_id}/store', [FeeController::class, 'store'])->name('manual.fee');
    Route::post('{event_id}/{booking_id}/delete', [FeeController::class, 'delete'])->name('fees.delete');
  });

  Route::post('/send-email', [EmailController::class, 'sendEmail'])->name('email.send');
  Route::get('/get-email-templates', [EmailController::class, 'getEmailTemplates'])->name('email.getTemplates');
  Route::get('/get-email-template', [EmailController::class, 'getEmailTemplate'])->name('email.getTemplate');
  Route::get('/show-email', [EmailController::class, 'showEmail'])->name('email.show');
  Route::get('/generate-booking-pdf', [EmailController::class, 'generateBookingPDF'])->name('email.generateBookingPDF');
  Route::get('/generate-img', [EmailController::class, 'generateBookingIMG'])->name('email.generateBookingIMG');
  Route::get('/generate-invoice-pdf', [EmailController::class, 'generateInvoicePDF'])->name('email.generateInvoicePDF');

  Route::get('/pricing-matrix/{eventId}/{cabinTypeId}', [PricingMatrixController::class, 'show'])->name('show.cabin-matrix');
});

Route::get('/join-organization', [OrganizationController::class, 'join'])->name('organization.join');
Route::put('/join-organization', [OrganizationController::class, 'join'])->name('organization.join');

Route::prefix('discounts')->group(function () {
  Route::post('{event_id}/{booking_id}/store', [DiscountsController::class, 'store'])->name('manual.discount');
  Route::post('{event_id}/{booking_id}/delete', [DiscountsController::class, 'delete'])->name('delete.discount');
});

Route::prefix('onboard-credit')->group(function () {
  Route::post('{event_id}/{booking_id}/store', [OnboardCreditController::class, 'store'])->name('manual.onboard-credit');
  Route::post('{event_id}/{booking_id}/delete', [OnboardCreditController::class, 'delete'])->name('delete.onboard-credit');
});

Route::prefix('payment-transfer')->group(function () {
    Route::post('{event_id}/{booking_id}/store', [\App\Http\Controllers\PaymentTransferController::class, 'store'])->name('manual.payment-transfer');
    Route::post('{event_id}/{booking_id}/delete', [\App\Http\Controllers\PaymentTransferController::class, 'delete'])->name('delete.payment-transfer');
});

Route::prefix('split-payment')->group(function () {
    Route::post('{event_id}/{booking_id}/store', [\App\Http\Controllers\SplitPaymentController::class, 'store'])
        ->name('manual.split-payment');
});

Route::get('/events/{id}/bookings-data', [BookingsController::class, 'getData'])->name('bookings.data');

Route::get('/customer-tags/paginated', [CustomerTagController::class, 'getPaginated'])->name('customer-tags.paginated');
Route::get('/customer-tags/create', [CustomerTagController::class, 'create'])->name('customer-tags.create');
Route::post('/customer-tags', [CustomerTagController::class, 'store'])->name('customer-tags.store');
Route::get('/customer-tags/{userTag}', [CustomerTagController::class, 'show'])
    ->where('userTag', '[0-9]+')
    ->name('customer-tags.show');
Route::get('/customer-tags/{userTag}/edit', [CustomerTagController::class, 'edit'])->name('customer-tags.edit');
Route::put('/customer-tags/{userTag}/update', [CustomerTagController::class, 'update'])->name('customer-tags.update');
Route::delete('/customer-tags/{userTag}', [CustomerTagController::class, 'destroy'])->name('customer-tags.destroy');
Route::get('/customer-tags', [CustomerTagController::class, 'index'])->name('customer-tags.index');
Route::get('/customers-search', [CustomerController::class, 'search'])->name('switch.lead.search');
Route::post('/events/{event_id}/booking/{booking_id}/switch-lead-passenger', [BookingsController::class, 'switchLeadPassenger'])->name('lead.passenger.switch');
Route::post('/events/{event_id}/booking/{booking_id}/switch-payment-plan', [BookingsController::class, 'switchPaymentPlan'])->name('bookings.switchPaymentPlan');

Route::resource('/tags', TagsController::class)
    ->only(['index', 'show','create', 'store', 'edit', 'update', 'destroy'])
    ->names([
        'index' => 'tags.index',
        'show' => 'tags.show',
        'create' => 'tags.create',
        'store' => 'tags.store',
        'edit' => 'tags.edit',
        'update' => 'tags.update',
        'destroy' => 'tags.destroy'
    ]);

Route::put('/users/{user}/avatar', [AgentController::class, 'update'])
        ->name('users.avatar.update');

Route::match(['GET', 'POST'], '/logs', [LogsController::class, 'index'])
    ->name('logs.index');

// --- API ROUTES FOR PASSPORT - DO NOT DELETE THIS ---
Route::prefix('oauth')->group(function () {
    // Login/Logout
    Route::get('/login', [PublicAuthController::class, 'index'])->name('oauth.login');
    Route::post('/login', [PublicAuthController::class, 'login'])->name('oauth.login.submit');
    // this is for web and admin context
    Route::post('/logout', [PublicAuthController::class, 'logoutWeb'])->name('oauth.web.logout');
    // this is for SPA context
    Route::get('/logout', [PublicAuthController::class, 'logoutSPA']);

    // Email Verification
    Route::get('/verify-email', [VerifyEmailController::class, 'index'])->name('oauth.email.verify.notice')->middleware('custom.auth.redirect');
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])->name('oauth.email.verify.resend');
    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware('signed')->name('oauth.email.verify.link');
});

$guard = config('passport.guard', null);
// END --- API ROUTES FOR PASSPORT - DO NOT DELETE THIS ---

require __DIR__ . '/auth.php';
