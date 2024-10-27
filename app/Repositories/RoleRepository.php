<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use GrahamCampbell\ResultType\Success;
use PhpParser\Node\Stmt\TryCatch;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
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

    public function getAll() {}

    public function find($id) {}

    public function create($data)
    {
        try {
            $data['guard_name'] = 'web';
            $role = Role::create($data);
            return $this->successResponse($role, 'Role created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update($data, $id)
    {
        try {
            $grantedPermissions = array_filter($data['permissions'], function ($permission) {
                return isset($permission['granted']) && $permission['granted'] === true;
            });
            $grantedPermissionNames = array_map(function ($permission) {
                return $permission['name'];
            }, $grantedPermissions);
            $role = Role::findById($data['id']);
            $s = $role->syncPermissions($grantedPermissionNames);
            return $this->successResponse([], 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete($role)
    {
        try {
            $role->delete();
        } catch (\Exception $e) {
           // dd($e->getMessage());
        }
    }

    public function addPermissionsToRole($role_id, $permission_id = null, $data = null)
    {
        try {
            $role = Role::find($role_id);
            if ($permission_id) {
                $permission = Permission::find($permission_id);
                $permission->assignRole($role);
            }
            if ($data) {
                $grantedPermissions = array_filter($data["permissions"], function ($permission) {
                    return isset($permission['granted']) && $permission['granted'] === true;
                });
                $grantedPermissionNames = array_map(function ($permission) {
                    return $permission['name'];
                }, $grantedPermissions);
                $role->syncPermissions($grantedPermissionNames);
                if ($role->name !== $data['name']) {
                    $role->name = $data['name'];
                    $role->save();
                }
            }
            return $this->successResponse([], 'Permission added to role successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function removePermission($role, $permission)
    {
        $role->revokePermissionTo($permission);
    }

    public function listByOrganization($org_id, $role_id = null, $user_id = null)
    {
        try {
            setPermissionsTeamId($org_id);
            if ($role_id) {
                $roles = Role::where(['team_id' => $org_id, 'id' => $role_id,])->orderBy('system', 'desc')->get();
            } else {
                $roles = Role::where(['team_id' => $org_id])->get();
            }
            if (isset($user_id)) {
                $roles->each(function ($role) use ($user_id) {
                    $role->granted = $role->users()->where('id', $user_id)->exists();
                    $role->user_id = $user_id;
                });
            }
            return $this->successResponse($roles, 'Roles listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function listPermissionByRole($id)
    {
        try {
            $role = Role::findById($id);
            $permissions = $role->permissions()->get();
            return $this->successResponse($permissions, 'Permissions listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function listByUser($id)
    {
        try {
            $user = User::find($id);
            $permissions = $user->getAllPermissions()->pluck('name');
            $roles = $user->getRoleNames();
            return $this->successResponse(array('permissions' => $permissions, 'roles' => $roles), 'Roles listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
