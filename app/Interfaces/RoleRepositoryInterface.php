<?php

namespace App\Interfaces;

interface RoleRepositoryInterface
{
    function getAll();
    function find($id);
    function create(array $data);
    function update(array $data,$id);
    function delete($id);
    function addPermissionsToRole(int $roleId, int $permissionId);
    function removePermission($role, $permission);
}