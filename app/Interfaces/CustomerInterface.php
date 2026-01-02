<?php

namespace App\Interfaces;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\User;

interface CustomerInterface {
    function getAll();

    function find($id);

    function save(array $data): ?User;

    function update(CustomerRequest $request, User $user);

    function store(CustomerRequest $request);

    function delete(User $user);

    function getAllCustomerData(int $perPage);

    function getPaginatedCustomerData($page, $perPage, $sortBy, $sortDir, $filters, $tags);

    function getBookingDataForCustomer(User $user);

    function addComment(User $customer, string $comment);

    function addTags(Customer $customer, array $tags);

    function deleteComment(User $customer, int $commentId);
}
