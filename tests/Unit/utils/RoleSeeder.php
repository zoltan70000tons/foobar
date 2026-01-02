<?php

namespace Tests\Unit\utils;

use App\Models\CabinType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder {
    public function run(): void {
        Role::updateOrCreate(
            ['id' => 7],
            ['team_id' => 1, 'name' => 'Customer', 'guard_name' => 'web', 'system' => true],
        );
    }
}
