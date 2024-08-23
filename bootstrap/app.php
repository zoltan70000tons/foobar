<?php

use App\Http\Middleware\ValidateOrganization;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();

    $middleware->alias([
      'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
      'auth.customer' => \App\Http\Middleware\AuthenticateCustomer::class,
      'ensure_not_customer' => \App\Http\Middleware\EnsureUserIsNotCustomer::class,
    ]);

    $middleware->web(append: [
      \App\Http\Middleware\HandleInertiaRequests::class,
      \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
      \App\Http\Middleware\TeamsPermission::class,
    ]);

    $middleware->api(prepend: [
      \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
      \App\Http\Middleware\EnsureUserIsNotWeb::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
