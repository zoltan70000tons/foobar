<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\EnvCheckCommand;

class EnvCheckServiceProvider extends ServiceProvider {
    public function register(): void {
        //
    }

    public function boot(): void {
        if ($this->app->runningInConsole()) {
            $this->commands([EnvCheckCommand::class]);
        }
    }
}
