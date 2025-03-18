<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class ExternalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Route::group([
            'namespace' => 'App\Http\Controllers\Notifications',
            'prefix' => 'api/notifications',
            'as' => 'api/notifications',
        ], function () {
            require base_path('routes/notifications.php');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
