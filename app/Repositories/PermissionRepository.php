<?php

namespace App\Repositories;

use App\Classes\ApiResponserHelper;
use App\Http\Resources\PermissionResource;
use App\Interfaces\PermissionRepositoryInterface;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Spatie\Permission\Models\Permission;


class PermissionRepository implements PermissionRepositoryInterface
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
        try {
            $data = Permission::all();
            return ApiResponserHelper::sendResponse(PermissionResource::collection($data), 'Permissions listed successfully', 200);
        } catch (\Exception $e) {
            return ApiResponserHelper::rollback($e);
        }
    }

    public function find($id)
    {
    }

    public function create($data)
    {
        try {
            $permission = Permission::create($data);
            return $this->successResponse($permission, 'Permissions created successfully');
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

    public function findByUser($id)
    {
        $user = User::find($id)->first();
        $permissionNames = $user->getPermissionNames();
        return $permissionNames;
    }

    public function findByRole($id)
    {
    }

    public function findbyOrganization($id)
    {
        $permissions =Permission::all();
       //$permissions = Permission::where(['organization_id' => $id])->get();
        return $this->successResponse($permissions, 'Permissions listed successfully');
    }


}
