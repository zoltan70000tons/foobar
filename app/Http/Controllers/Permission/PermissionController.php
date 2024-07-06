<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Interfaces\PermissionRepositoryInterface;
use App\Repositories\PermissionRepository;


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

    
    public function update()
    {
    }

    public function delete()
    {
    }
}
