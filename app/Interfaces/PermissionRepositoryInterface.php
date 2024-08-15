<?php

namespace App\Interfaces;

use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface
{
    function getAll();
    function find($id);
    function create(array $data);
    function update(Permission $permission,$data);
    function delete(Permission $permission);
    function findByUser($id);
    function findbyRole($id);
    function findbyOrganization($id,$role_id = null);
}
