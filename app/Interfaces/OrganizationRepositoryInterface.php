<?php

namespace App\Interfaces;

interface OrganizationRepositoryInterface
{
    function getAll();
    function find($id);
    function save(array $data);
    function update(array $data,$id);
    function delete($id);
}
