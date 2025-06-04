<?php

namespace App\Interfaces;

use App\Http\Requests\CustomerTagRequest;
use App\Models\UserTag;

interface CustomerTagInterface
{
    function getAll();

    function find($id);

    function save(array $data): ?UserTag;

    function update(CustomerTagRequest $request, UserTag $userTag);

    function store(CustomerTagRequest $request);

    function delete(UserTag $userTag);
}
