<?php

namespace App\Interfaces;

use App\Models\CabinCategory;

interface CabinCategoryInterface
{
    function getAll();
    function find($id);
    function save(array $data): ?CabinCategory;
    function update(array $data,$id);
    function delete($id);
}
