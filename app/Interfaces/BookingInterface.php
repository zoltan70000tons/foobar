<?php

namespace App\Interfaces;

use App\Models\Booking;

interface BookingInterface
{
    function getAll();
    function find($id);
    function findByCode($code);
    function save(array $data): ?Booking;
    function update(array $data,$id);
    function delete($id);
    function addTags(Booking $booking, $tags);
    function getByTag($tag, $keyword = null);
    function getByStatus($status, $keyword = null);
    function assignAgent($code, $user);
    function changeCabin(Booking $booking, $cabin_number);
    function changeCode(Booking $booking, $new_code);
    function changeStatus(Booking $booking, $status);
    function addComment(Booking $booking, $comment);
}
