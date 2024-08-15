<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\PermissionResource;
use App\Interfaces\PermissionRepositoryInterface;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\Redirect;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepository implements PermissionRepositoryInterface
{
    use JsonResponseTrait;

    protected $organizationId;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->organizationId = config('settings.organization_id');
        setPermissionsTeamId($this->organizationId);
    }

    public function getAll()
    {
        try {
            $data = Permission::all();
            return $this->successResponse($data, 'Permissions listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function find($id) {}

    public function create($data)
    {
        try {
            $data['guard_name'] = 'web';
            $permission = Permission::create($data);
            return $this->successResponse($permission, 'Permissions created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Permission $permission, $data) {
        $name = $data['name'];
        $permission->update($data);
    }

    public function delete($permission)
    {
        try {
            $permission->delete();
        } catch (\Exception $e) {
           // dd($e->getMessage());
        }
    }

    public function findByUser($id)
    {
        $user = User::find($id)->first();
        $permissionNames = $user->getPermissionNames();
        return $permissionNames;
    }

    public function findByRole($id) {}

    public function findbyOrganization($id, $role_id = null)
    {
        try {
            if ($role_id) {
                $role = Role::with('permissions')
                    ->where(['team_id' => $id, 'id' => $role_id])
                    ->firstOrFail();
                $organizationPermissions = Permission::all();
                $permissions = $organizationPermissions->map(function ($permission) use ($role) {
                    return [
                        'name' => $permission->name,
                        'granted' => $role->permissions->contains('name', $permission->name),
                    ];
                });
            } else {
                $permissions = Permission::all();
            }
            return $this->successResponse($permissions, 'Permissions listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
