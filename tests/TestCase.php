<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @mixin \Tests\Support\ActsAsAgent
 * @method \App\Models\User loginManager(\App\Models\User $user = null)
 * @method \App\Models\User loginAgent(\App\Models\User $user = null)
 * @property \App\Models\Booking $booking
 */

abstract class TestCase extends BaseTestCase {
    //
}
