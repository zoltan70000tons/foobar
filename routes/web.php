<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\CabinCategoriesController;
use App\Http\Controllers\CabinsController;
use App\Http\Controllers\DeletedController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MailTestController;
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
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\PaymentController;

Route::get("/", function () {
  return Inertia::render("Welcome", [
    "canLogin" => Route::has("login"),
    "canRegister" => Route::has("register"),
    "laravelVersion" => Application::VERSION,
    "phpVersion" => PHP_VERSION,
  ]);
});

Route::get("/token", function () {
  return csrf_token();
});

Route::post("/contact/submit", [ContactFormController::class, "submit"]);

Route::get("/dashboard", function () {
  return Inertia::render("Dashboard");
})
  ->middleware(["auth", "verified"])
  ->name("dashboard");

Route::middleware("auth")->group(function () {
  Route::get("/profile", [ProfileController::class, "edit"])->name("profile.edit");
  Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
  Route::delete("/profile", [ProfileController::class, "destroy"])->name("profile.destroy");

  Route::get("/{slug}/invitations/create", [InvitationController::class, "create"])->name("invitations.create");
  Route::post("/{slug}/invitations", [InvitationController::class, "store"])->name("invitations.store");

  Route::get("/team", function () {
    return Inertia::render("Teams");
  })->name("teams");

  Route::get("/team/roles", function () {
    return Inertia::render("ManageRole");
  })->name("roles");

  Route::get("/team/permissions", function () {
    return Inertia::render("ManagePermission");
  })->name("permissions");

  Route::apiResource("/roles", RoleController::class);
  Route::apiResource("/permissions", PermissionController::class);
  Route::post("/permissions/addToRole", "App\Http\Controllers\Role\RoleController@addPermissionToRole");
  Route::apiResource("/organizations", OrganizationController::class);
  Route::get("/organization/permissions", "App\Http\Controllers\Permission\PermissionController@listByOrganization");
  Route::get("/organization/roles", "App\Http\Controllers\Role\RoleController@listByOrganization");
  Route::get("/organization/getTeam", "App\Http\Controllers\Api\TeamController@listMembersByOrganization");
  Route::put("/organization/members/updateRole", "App\Http\Controllers\Api\TeamController@updateMemberRoles")->name('member.updateRole');
  Route::get("/users/getPermissions", "App\Http\Controllers\Role\RoleController@getPermissions");
  Route::post("/team/send-invitations", "App\Http\Controllers\InvitationController@store");
  Route::post("/member/update", "App\Http\Controllers\Api\TeamController@updateMember")->name("member.update");

  //There is a conflict between create and show routes,
  //so to make it work correctly, it should be overridden before defining the Route::resource
  //Events crud
  Route::get("/events/create", [EventController::class, "create"])->name("events.create");
  Route::get("/events/{event}", [EventController::class, "show"])->name("events.show");
  Route::resource("/events", EventController::class);

  Route::get("/events/{id}/cabins", [CabinsController::class, "index"])->name("cabins.index");
  Route::get("/events/{id}/cabins/categories", [CabinCategoriesController::class, "index"])->name("cabins.categories");
  Route::get("/events/{id}/cabins/tags", [TagsController::class, "index"])->name("cabins.tags");

  //Cabin categories
  Route::get("/events/{id}/cabins/categories/{catId}/show", [CabinCategoriesController::class, "show"])->name(
    "cabinCategory.show"
  );
  Route::get("/events/{id}/cabins/categories/create", [CabinCategoriesController::class, "create"])->name(
    "cabinCategory.create"
  );
  Route::post("/events/{id}/cabins/categories/store", [CabinCategoriesController::class, "store"])->name(
    "cabinCategory.store"
  );
  Route::get("/events/{id}/cabins/categories/{catId}/edit", [CabinCategoriesController::class, "edit"])->name(
    "cabinCategory.edit"
  );
  Route::post("/events/{id}/cabins/categories/{catId}/update", [CabinCategoriesController::class, "update"])->name(
    "cabinCategory.update"
  );
  Route::delete("/events/{id}/cabins/categories/{catId}/delete", [CabinCategoriesController::class, "destroy"])->name(
    "cabinCategory.destroy"
  );

  //Cabins Add Tag
  Route::post("/events/{id}/cabins/addTag", [CabinsController::class, "addTag"])->name("cabins.addTag");
  Route::post("/events/{id}/cabins/updateStatus", [CabinsController::class, "updateStatus"])->name(
    "cabins.updateStatus"
  );

  //Cabin edit
  Route::get("/events/{id}/cabins/{cabin_id}/edit", [CabinsController::class, "edit"])->name("cabins.edit");
  Route::post("/events/{id}/cabins/{cabin_id}/update", [CabinsController::class, "update"])->name("cabins.update");

  Route::post('/events/{id}/bookings/createManual', [BookingsController::class, 'store'])
  ->name('bookings.createManual');
    //Booking controller
    Route::get('/events/{id}/bookings', [BookingsController::class, 'index'])
    ->where('id', '[0-9]+|all') 
    ->name('bookings.index');
    Route::post('/events/{id}/bookings/update-cabin', [BookingsController::class, 'cabinUpdate'])
    ->name('bookings.updateCabin');
    Route::post('/events/{id}/bookings/update-code', [BookingsController::class, 'codeUpdate'])
    ->name('bookings.updateCode');
    Route::post('/events/{id}/bookings/update-status', [BookingsController::class, 'statusUpdate'])
    ->name('bookings.updateStatus');
    Route::post('/events/{id}/bookings/add-comment', [BookingsController::class, 'addComment'])
    ->name('bookings.addComment');
    Route::post('/events/{id}/bookings/update-tags', [BookingsController::class, 'updateTags'])
    ->name('bookings.updateTags');
    Route::get('/events/{id}/bookings/{booking_code}', [BookingsController::class, 'show'])
    ->name('bookings.show');
    Route::post('/events/{id}/bookings/{booking_code}', [BookingsController::class, 'update'])
    ->name('bookings.update');
    Route::put('/bookings/{booking}/assign-agent', [BookingsController::class, 'assignAgent'])->name('bookings.assignAgent');
    Route::get('/bookings/edit-mode', [BookingsController::class, 'editMode'])->name('bookings.editMode');
    Route::post('/events/{id}//bookings/cancel', [BookingsController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/cabins/available', [BookingsController::class, 'getAvailableCabins'])->name('cabins.available');


    Route::get('/not-allowed', [NotAllowedController::class, 'index'])->name('access.denied');
    Route::get('/menu/bookings', [MenuController::class, 'getEvents'])->name('menu.bookings');
    Route::get('/passengers/search', [PassengerController::class, 'search'])->name('seat.search');

    Route::prefix('/events/{id}/booking/{booking_id}/passengers')->group(function () {
        Route::post('/update/seat', [PassengerController::class, 'updateSeat'])->name('seat.update');
        Route::post('/release/seat', [PassengerController::class, 'releaseSeat'])->name('seat.release');
    });

  Route::get("/not-allowed", [NotAllowedController::class, "index"])->name("access.denied");
  Route::get("/menu/bookings", [MenuController::class, "getEvents"])->name("menu.bookings");

  Route::prefix("passengers")->group(function () {
    Route::post("/add", [PassengerController::class, "addPassenger"]);
    Route::post("/edit/{id}", [PassengerController::class, "editPassenger"]);
    Route::get("/validate", [PassengerController::class, "validatePassenger"]);
    Route::get("/search", [PassengerController::class, "search"]);
  });

  Route::prefix("adjustments")->group(function () {
    Route::post("{event_id}/{booking_id}/add-to-booking", [AdjustmentsController::class, "createAdjustment"])->name("bookings.createAdjustment");
    Route::post("{event_id}/{booking_id}/delete", [AdjustmentsController::class, "deleteAdjustment"])->name("bookings.deleteAdjustment");
    Route::post("{event_id}/{booking_id}/update", [AdjustmentsController::class, "updateAdjustment"])->name("bookings.updateAdjustment");
  });

  Route::prefix("payments")->group(function () {
    Route::post("{event_id}/{booking_id}/store", [PaymentController::class, "store"])->name("manual.payment");
    Route::post("{event_id}/{booking_id}/payment/delete", [PaymentController::class, "delete"])->name("payments.delete");
  });

  Route::prefix("fees")->group(function () {
    Route::post("{event_id}/{booking_id}/store", [FeeController::class, "store"])->name("manual.fee");
    Route::post("{event_id}/{booking_id}/delete", [FeeController::class, "delete"])->name("fees.delete");
  });

  Route::post('/send-email', [EmailController::class, 'sendEmail'])->name('email.send');
  Route::get('/get-email-templates', [EmailController::class, 'getEmailTemplates'])->name('email.getTemplates');
  Route::get('/get-email-template', [EmailController::class, 'getEmailTemplate'])->name('email.getTemplate');
  Route::get('/show-email', [EmailController::class, 'showEmail'])->name('email.show');
  Route::get('/generate-booking-pdf', [EmailController::class, 'generateBookingPDF'])->name('email.generateBookingPDF');
  Route::get('/generate-img', [EmailController::class, 'generateBookingIMG'])->name('email.generateBookingIMG');


});



Route::get("/join-organization", [OrganizationController::class, "join"])->name("organization.join");
Route::put("/join-organization", [OrganizationController::class, "join"])->name("organization.join");

// Route::get('/register-organization', function () {
//     return Inertia::render('RegisterOrganization');
// })->name('organization.register');

//Route::get('/send-test-email', [MailTestController::class, 'sendMail']);

Route::get("/test-broadcast", function () {
  broadcast(new TestMessageSent("Este es un mensaje de prueba."));
  return "Mensaje enviado";
});

require __DIR__ . "/auth.php";
