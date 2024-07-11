<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\addPermissionToRoleRequest;
use App\Http\Requests\Role\ListRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Repositories\RoleRepository;


class RoleController extends Controller
{
    private RoleRepositoryInterface $rolesRepositoryInterface;

    public function __construct(RoleRepository $rolesRepositoryInterface) {
        $this->rolesRepositoryInterface = $rolesRepositoryInterface;
    }

    public function index(CreateRoleRequest $request){

    }
    

    public function store(CreateRoleRequest $request){
        $data = $request->all();
        return $this->rolesRepositoryInterface->create($data);
    }
    
    public function addPermissionToRole(addPermissionToRoleRequest $request){
        $data= $request->all();
        return $this->rolesRepositoryInterface->addPermissionsToRole($data['roleId'],$data['permissionId']);
    }

    public function listByOrganization(ListRoleRequest $request)
    {
      $id = $request->id;
      return $this->rolesRepositoryInterface->listByOrganization($id);
    }
}
