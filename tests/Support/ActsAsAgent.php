<?php

namespace Tests\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;

trait ActsAsAgent
{
    protected function loginAgent(?User $user = null): User
    {
        app('Spatie\Permission\PermissionRegistrar'::class)->setPermissionsTeamId(1);
        $user ??= User::factory()->create();
        $agentRole = Role::where('name', 'Agent')->firstOrFail();
        $user->assignRole($agentRole);
        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user, 'web');
        return $user;
    }
}