<?php

namespace App\Interfaces;

interface RoleRepositoryInterface
{
    function getAll();
    function find($id);
    function create(array $data);
    function update(array $data,$id);
    function delete($id);
    function addPermissionsToRole(int $role_id, int $permission_id = null, $data=null);
    function removePermission($role, $permission);
    function listByOrganization($org_id, $role_id = null,$user_id=null);
    function listByUser($id);

}