<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Traits\JsonResponseTrait;
use GrahamCampbell\ResultType\Success;
use PhpParser\Node\Stmt\TryCatch;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    use JsonResponseTrait;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getAll()
    {
    }

    public function find($id)
    {
    }

    public function create($data)
    {
        try {
            $role = Role::create($data);
            return $this->successResponse($role, 'Role created successfully');
        } catch (\Exception $e) {
            return ApiResponserHelper::rollback($e);
        }
    }

    public function update($data, $id)
    {
        try {
            $permiss = Role::findByName('test')->permissions;
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
            dd($e->getMessage());
        }
    }

    public function delete($id)
    {
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
            dd($e->getMessage() . '  ' . $e->getLine());
        }
    }

    public function removePermission($role, $permission)
    {
        $role->revokePermissionTo($permission);
    }

    public function listByOrganization($org_id, $role_id = null, $user_id = null)
    {
        try {
            setPermissionsTeamId(1);
            if ($role_id) {
                $roles = Role::where(['team_id' => $org_id, 'id' => $role_id,])->orderBy('system', 'desc')->get();
            } else {
                $roles = Role::where(['team_id' => $org_id])->get();
            }
            if (is_numeric($user_id)) {
                $roles->each(function ($role) use ($user_id) {
                    $role->granted = $role->users()->where('id', $user_id)->exists();
                    $role->user_id = $user_id;
                });
            }
            return $this->successResponse($roles, 'Roles listed successfully');
        } catch (\Exception $e) {
            dd($e->getMessage());
            //return ApiResponserHelper::rollback($e);
        }
    }

    public function listPermissionByRole($id)
    {
        $role = Role::findById($id);
        $permissions = $role->permissions()->get();
        return $this->successResponse($permissions, 'Permissions listed successfully');
    }
}
