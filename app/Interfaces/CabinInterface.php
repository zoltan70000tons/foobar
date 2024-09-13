<?php

namespace App\Interfaces;

use App\Models\Cabin;

interface CabinInterface
{
    function getAll();
    function find($id);
    function save(array $data): ?Cabin;
    function update(array $data,$id);
    function delete($id);
    function getCategoriesAndCabins();
}
