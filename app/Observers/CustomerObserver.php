<?php

// app/Observers/CustomerObserver.php
namespace App\Observers;

use App\Enums\GlobalLog\LogActionCustomer;
use App\Models\User;
use App\Support\GlobalLogger;

class CustomerObserver
{
    //same as app/Repositories/CustomerRepository.php:172 - pick what you like best
    //Currently the CustomerRepository was picked - Zoltan
    /*public function updated(User $customer): void
    {
        // Reload all relationships to get latest values
        $customer->load([
            'tags',
            'detail',
            'survivorNumber',
            'customerAddress',
        ]);

        // Prepare 'before' data from original attributes
        $before = [
            // User
            'email' => $customer->getOriginal('email'),
            'username' => $customer->getOriginal('username'),
            'is_survivor' => $customer->getOriginal('is_survivor'),

            // User Detail
            'first_name' => optional($customer->detail)->getOriginal('first_name'),
            'middle_name' => optional($customer->detail)->getOriginal('middle_name'),
            'last_name' => optional($customer->detail)->getOriginal('last_name'),
            'gender' => optional($customer->detail)->getOriginal('gender'),
            'citizenship' => optional($customer->detail)->getOriginal('citizenship'),
            'phone' => optional($customer->detail)->getOriginal('phone'),
            'emergency_c_name' => optional($customer->detail)->getOriginal('emergency_c_name'),
            'emergency_c_phone' => optional($customer->detail)->getOriginal('emergency_c_phone'),
            'language' => optional($customer->detail)->getOriginal('language'),
            'dob' => optional($customer->detail)->getOriginal('dob'),

            // Customer Address
            'address_first' => optional($customer->customerAddress)->getOriginal('address_first'),
            'address_second' => optional($customer->customerAddress)->getOriginal('address_second'),
            'city' => optional($customer->customerAddress)->getOriginal('city'),
            'state' => optional($customer->customerAddress)->getOriginal('state'),
            'postal_code' => optional($customer->customerAddress)->getOriginal('postal_code'),
            'country' => optional($customer->customerAddress)->getOriginal('country'),

            // Survivor Number
            'survivor_number' => optional($customer->survivorNumber)->getOriginal('survivor_number'),

            // Tags — this one may not track changes well unless you're syncing them manually
            'tags' => $customer->tags->pluck('name')->toArray(), // No "before" unless you track it yourself
        ];

        // Prepare 'after' data
        $after = [
            'email' => $customer->email,
            'username' => $customer->username,
            'is_survivor' => $customer->is_survivor,

            'first_name' => $customer->detail->first_name ?? null,
            'middle_name' => $customer->detail->middle_name ?? null,
            'last_name' => $customer->detail->last_name ?? null,
            'gender' => $customer->detail->gender ?? null,
            'citizenship' => $customer->detail->citizenship ?? null,
            'phone' => $customer->detail->phone ?? null,
            'emergency_c_name' => $customer->detail->emergency_c_name ?? null,
            'emergency_c_phone' => $customer->detail->emergency_c_phone ?? null,
            'language' => $customer->detail->language ?? null,
            'dob' => $customer->detail->dob ?? null,

            'address_first' => $customer->customerAddress->address_first ?? null,
            'address_second' => $customer->customerAddress->address_second ?? null,
            'city' => $customer->customerAddress->city ?? null,
            'state' => $customer->customerAddress->state ?? null,
            'postal_code' => $customer->customerAddress->postal_code ?? null,
            'country' => $customer->customerAddress->country ?? null,

            'survivor_number' => $customer->survivorNumber->survivor_number ?? null,

            'tags' => $customer->tags->pluck('name')->toArray(),
        ];

        // Log the update
        GlobalLogger::log(
            LogActionCustomer::CUSTOMER_UPDATED,
            'customer',
            $customer->id,
            sprintf('Customer updated (%s)', $customer->email),
            [
                'before' => $before,
                'after' => $after,
            ]
        );
    }*/

    //Does not work now, since we don't delete, but update the email to deleted_....@....
    public function deleted(User $customer): void
    {
        // Reload all relationships to get latest values
        $customer->load([
            'tags',
            'detail',
            'survivorNumber',
            'customerAddress',
        ]);

        // Prepare 'before' data from original attributes
        $before = [
            // User
            'email' => $customer->getOriginal('email'),
            'username' => $customer->getOriginal('username'),
            'is_survivor' => $customer->getOriginal('is_survivor'),

            // User Detail
            'first_name' => optional($customer->detail)->getOriginal('first_name'),
            'middle_name' => optional($customer->detail)->getOriginal('middle_name'),
            'last_name' => optional($customer->detail)->getOriginal('last_name'),
            'gender' => optional($customer->detail)->getOriginal('gender'),
            'citizenship' => optional($customer->detail)->getOriginal('citizenship'),
            'phone' => optional($customer->detail)->getOriginal('phone'),
            'emergency_c_name' => optional($customer->detail)->getOriginal('emergency_c_name'),
            'emergency_c_phone' => optional($customer->detail)->getOriginal('emergency_c_phone'),
            'language' => optional($customer->detail)->getOriginal('language'),
            'dob' => optional($customer->detail)->getOriginal('dob'),

            // Customer Address
            'address_first' => optional($customer->customerAddress)->getOriginal('address_first'),
            'address_second' => optional($customer->customerAddress)->getOriginal('address_second'),
            'city' => optional($customer->customerAddress)->getOriginal('city'),
            'state' => optional($customer->customerAddress)->getOriginal('state'),
            'postal_code' => optional($customer->customerAddress)->getOriginal('postal_code'),
            'country' => optional($customer->customerAddress)->getOriginal('country'),

            // Survivor Number
            'survivor_number' => optional($customer->survivorNumber)->getOriginal('survivor_number'),

            // Tags — this one may not track changes well unless you're syncing them manually
            'tags' => $customer->tags->pluck('name')->toArray(), // No "before" unless you track it yourself
        ];

        // Log the update
        GlobalLogger::log(
            LogActionCustomer::CUSTOMER_DELETED,
            'customer',
            $customer->id,
            sprintf('Customer updated (%s)', $customer->email),
            [
                'before' => $before,
            ]
        );
    }
}
