<?php

namespace App\Interfaces;

interface EventRepositoryInterface
{
    function getAll();
    function find($id);
    function save(array $data);
    function update(array $data,$id);
    function delete($id);
    function listMenu();
}
