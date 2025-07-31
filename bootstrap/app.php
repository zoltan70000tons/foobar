<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up'
  )
  ->withMiddleware(function (Middleware $middleware) {
    // $middleware->statefulApi();
     $middleware->authenticateSessions();
    $middleware->encryptCookies(except: ['email_verified']);

    $middleware->alias([
      'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
      'membership_sales' => \App\Http\Middleware\MembershipSales::class,
      // 'auth.customer' => \App\Http\Middleware\AuthenticateCustomer::class,
      //'ensure_not_customer' => \App\Http\Middleware\EnsureUserIsNotCustomer::class,
      'clear_expired_reservation' => \App\Http\Middleware\ClearExpiredReservation::class,
      'one_booking_per_user' => \App\Http\Middleware\OneBookingPerUser::class,
      'booking_status' => \App\Http\Middleware\BookingStatusMiddleware::class,
      'allowed_domains' => \App\Http\Middleware\CheckAllowedDomains::class,
      'custom.auth.redirect' => \App\Http\Middleware\RedirectIfUnauthenticatedToOAuthLogin::class,
      //'check_booking_session' => \App\Http\Middleware\CheckBookingSession::class,
      'electron_auth' => \App\Http\Middleware\ElectronAuth::class,
    ]);

    $middleware->web(
      append: [
        // authenticateSessions
        \App\Http\Middleware\TeamContext::class,
        \App\Http\Middleware\HandleInertiaRequests::class,
        \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        \App\Http\Middleware\TeamsPermission::class,
        //\App\Http\Middleware\EnsureUserIsNotCustomer::class,
      ]
    );

    $middleware->api(
      prepend: [
        //\App\Http\Middleware\RestoreCartMiddleware::class,
        //\App\Http\Middleware\TeamsPermission::class,
        //\App\Http\Middleware\ApiRedirectHttp::class,
        \App\Http\Middleware\TeamContext::class,
       // \Illuminate\Session\Middleware\StartSession::class,
        //\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // \App\Http\Middleware\EnsureUserIsNotWeb::class,
      ]
    );

    $middleware->validateCsrfTokens(except: [
      '/api/auth/login',
      '/api/auth/logout',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })
  ->create();
