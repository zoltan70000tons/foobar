<?php

namespace Tests\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait ActsAsAgent {
    public function loginAgent(?User $user = null): User {
        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        $user ??= User::factory()->create();
        $agentRole = Role::where('name', 'Agent')->firstOrFail();
        $user->assignRole($agentRole);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user, 'web');
        return $user;
    }

    public function loginManager(?User $user = null): User {
        app(PermissionRegistrar::class)->setPermissionsTeamId(1);
        $user ??= User::factory()->create();
        $adminRole = Role::where('name', 'Manager')->firstOrFail();
        $user->assignRole($adminRole);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user, 'web');
        return $user;
    }
}
