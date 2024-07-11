<?php

namespace App\Interfaces;

interface PermissionRepositoryInterface
{
    function getAll();
    function find($id);
    function create(array $data);
    function update(array $data,$id);
    function delete($id);
    function findByUser($id);
    function findbyRole($id);
    function findbyOrganization($id);
}
