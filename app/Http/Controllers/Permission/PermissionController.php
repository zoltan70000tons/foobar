<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\DeletePermissionRequest;
use App\Http\Requests\Permission\ListPermissionRequest;
use App\Interfaces\PermissionRepositoryInterface;
use App\Repositories\PermissionRepository;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private PermissionRepositoryInterface $permissionRepositoryInterface;

    public function __construct(PermissionRepository $permissionRepositoryInterface)
    {
        $this->permissionRepositoryInterface = $permissionRepositoryInterface;
    }

    public function index()
    {
        return $this->permissionRepositoryInterface->getAll();
    }

    public function store(CreatePermissionRequest $request)
    {
        $data = $request->all();
        return $this->permissionRepositoryInterface->create($data);
    }



    public function destroy(Permission $permission)
    {
      $this->permissionRepositoryInterface->delete($permission);
      //Redirect::route('permissions.index')->with('flash', 'Permission updated successfully.');
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->permissionRepositoryInterface->update($permission, $request->all());

        //Redirect::route('permissions.index')->with('success', 'Permission updated successfully.');
    }


    public function listByOrganization(ListPermissionRequest $request)
    {
      $id = $request->id;
      $role_id = $request->role_id;
      return $this->permissionRepositoryInterface->findbyOrganization($id,$role_id);
    }
}
