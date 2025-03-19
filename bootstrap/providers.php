<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\ExternalServiceProvider::class,
    App\Providers\HelpersServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Propaganistas\LaravelPhone\PhoneServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];
