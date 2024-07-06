<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
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
            return ApiResponserHelper::sendResponse(RoleResource::collection($data), 'Role created successfully', 200);
        } catch (\Exception $e) {
            return ApiResponserHelper::rollback($e);
        }   
    }

    public function update($data, $id)
    {
       
    }

    public function delete($id)
    {
    }

    public function addPermissionsToRole($roleId, $permissionId)
    {
        try {
            $permission = Permission::findById($permissionId);
            $role = Role::findById($roleId);
            $permission->assignRole($role);
            return ApiResponserHelper::sendResponse(RoleResource::collection([]), 'Permission added to role successfully', 200);
        } catch (\Exception $e) {
            return ApiResponserHelper::rollback($e);
        }
    }

    public function removePermission($role, $permission)
    {
        $role->revokePermissionTo($permission);
    }
}
