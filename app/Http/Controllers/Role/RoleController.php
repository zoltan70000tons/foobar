<?php

namespace App\Http\Controllers\Role;

use App\Enums\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\ListUserPermissionRequest;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\AddPermissionToRoleRequest;
use App\Http\Requests\Role\ListRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Models\Organization;
use App\Repositories\RoleRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Facades\Redirect;
use PhpParser\Node\Stmt\TryCatch;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;
  private RoleRepositoryInterface $rolesRepositoryInterface;
  private $organizationId;

  public function __construct(RoleRepository $rolesRepositoryInterface)
  {
    $this->rolesRepositoryInterface = $rolesRepositoryInterface;
    $this->organizationId = config('settings.organization_id');
  }

  public function index(CreateRoleRequest $request) {}


  public function store(CreateRoleRequest $request)
  {
    try {
      return $this->withPermission(
        [Permissions::CreateRoles],
        function ($request) {
          $data = $request->all();
          return $this->rolesRepositoryInterface->create($data);
        },
        $request
      );
    } catch (\Exception $e) {
      //throw $th;
    }
  }

  public function addPermissionToRole(AddPermissionToRoleRequest $request, $data = null)
  {
    try {
      return $this->withPermission(
        [Permissions::AssignPermissions],
        function ($request) {
          $data = $request->all();
          return $this->rolesRepositoryInterface->addPermissionsToRole($data['role_id'], $data['permission_id'] ?? NULL, $data ?? NULL);
        },
        $request
      );
    } catch (\Throwable $th) {
      //throw $th;
    }
  }

  public function listByOrganization(ListRoleRequest $request)
  {
    try {
      return $this->withPermission(
        [Permissions::ViewRoles],
        function ($request) {
          $role_id = $request->role_id;
          $user_id = $request->user_id;
          return $this->rolesRepositoryInterface->listByOrganization($this->organizationId, $role_id, $user_id);
        },
        $request
      );
    } catch (\Exception $e) {
      //throw $th;
    }

  }


  public function update(UpdateRoleRequest $request)
  {
    try {
      return $this->withPermission(
        [Permissions::EditRoles],
        function ($request) {
          $fields = $request->all();
          return $this->rolesRepositoryInterface->update($fields, $fields['id']);
        },
        $request
      );
    } catch (\Exception $e) {
      //throw $th;
    }
    
  }
  public function destroy(Role $role)
  {
   try {
    return $this->withPermission(
      [Permissions::DeleteRoles],
      function ($role) {
        $this->rolesRepositoryInterface->delete($role);
        Redirect::route('roles.index')->with('flash', 'Role deleted successfully.');
      },
      $role
    );
   } catch (\Exception $e) {
    //throw $th;
   }

  }

  public function getPermissions(ListUserPermissionRequest $request)
  {
    try {
      return $this->withPermission(
        [Permissions::DeleteRoles],
        function ($request) {
          $fields = $request->all();
          return $this->rolesRepositoryInterface->listByUser($fields['id']);
        },
        $request
      );
    } catch (\Exception $e) {
      //throw $th;
    }
    

  }
}
