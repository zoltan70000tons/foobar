<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\ListUserPermissionRequest;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\addPermissionToRoleRequest;
use App\Http\Requests\Role\ListRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Organization;
use App\Repositories\RoleRepository;


class RoleController extends Controller
{
    private RoleRepositoryInterface $rolesRepositoryInterface;
    private $organizationId ;

    public function __construct(RoleRepository $rolesRepositoryInterface) {
        $this->rolesRepositoryInterface = $rolesRepositoryInterface;
        $this->organizationId = config('settings.organization_id');

    }

    public function index(CreateRoleRequest $request){

    }
    

    public function store(CreateRoleRequest $request){
        $data = $request->all();
        return $this->rolesRepositoryInterface->create($data);
    }
    
    public function addPermissionToRole(addPermissionToRoleRequest $request, $data=null){
        $data= $request->all();
        return $this->rolesRepositoryInterface->addPermissionsToRole($data['role_id'],$data['permission_id']?? NULL, $data??NULL);
    }
      
    public function listByOrganization(ListRoleRequest $request)
    {
        $role_id = $request->role_id;
        $user_id =$request->user_id;
      return $this->rolesRepositoryInterface->listByOrganization($this->organizationId,$role_id,$user_id);
    }


    public function update(UpdateRoleRequest $request){
      $fields = $request->all();
      return $this->rolesRepositoryInterface->update($fields,$fields['id']);
    }

    public function getPermissions(ListUserPermissionRequest $request){
        $fields = $request->all();
        return $this->rolesRepositoryInterface->listByUser($fields['id']);
    }
}
