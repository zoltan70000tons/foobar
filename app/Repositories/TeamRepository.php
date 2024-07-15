<?php

namespace App\Repositories;

use App\Interfaces\TeamRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TeamRepository implements TeamRepositoryInterface
{
    use JsonResponseTrait;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAllMembers($org_id, $team = null)
    {
        setPermissionsTeamId($org_id);
        $org = Organization::find($org_id);
        if ($org) {
            $members = $org->users()->with('roles')->get();
            $result = $members->map(function ($user) use ($org) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => 'Activo',
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'organization_id' => $org->id,
                    'organization_name' => $org->name,
                ];
            });

            return $result;
        }
        return null;
    }

    public function findMember($team, $id)
    {
    }

    public function updateMemberRoles($user_id, $org_id, $roles)
    {
        try {
            setPermissionsTeamId($org_id);
            $user = User::find($user_id);
            $user->syncRoles($roles);
            return $this->successResponse($roles, 'Roles updated successfully');
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
    }
}
