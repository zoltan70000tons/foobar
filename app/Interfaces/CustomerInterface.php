<?php

namespace App\Interfaces;

use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerRequest;
use App\Models\User;

interface CustomerInterface
{
    function getAll();

    function find($id);

    function save(array $data): ?User;

    function update(CustomerRequest $request, User $user);

    function store(CustomerCreateRequest $request);

    function delete(User $user);

    function getAllCustomerData();

    function getBookingDataForCustomer(User $user);
}
