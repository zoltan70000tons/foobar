<?php

namespace App\Interfaces;

use App\Models\Booking;

interface BookingInterface
{
    function getAll();
    function find($id);
    function save(array $data): ?Booking;
    function update(array $data,$id);
    function delete($id);
    function addTags(array $tags, array $cabins);
    function getByTag($tag);
}
