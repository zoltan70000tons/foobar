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
    function getCategoriesAndCabins(int $event_id,?int $perPage = null, ?int $page = null, ?string $code =null, ?string $name =null);
    function addTags(array $tags, array $cabins);
    function getTypes();
    function getSharedCabins(int $eventId, int $cabinSpecId);
    function createShared(int $cabinId,int $categoryId);
}
