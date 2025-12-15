<?php

namespace App\Interfaces;

use App\Models\CabinCategory;

interface CabinCategoryInterface
{
  function getAll();
  function find($id);
  function save(array $data): ?CabinCategory;
  function update(array $data, $id);
  function delete($id);
  function getCategoriesByEvent($event_id);
  function getCategoriesWithCabins($event_id);
}
