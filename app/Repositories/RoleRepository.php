<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\RoleResource;
use App\Interfaces\RoleRepositoryInterface;
use App\Traits\JsonResponseTrait;
use GrahamCampbell\ResultType\Success;
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
            return $this->successResponse([], 'Permission added to role successfully');
        } catch (\Exception $e) {
            return ApiResponserHelper::rollback($e);
        }
    }

    public function removePermission($role, $permission)
    {
        $role->revokePermissionTo($permission);
    }

    public function listByOrganization($id)
    {
        $roles =Role::all();
         return $this->successResponse($roles, 'Roles listed successfully');
    }
}
