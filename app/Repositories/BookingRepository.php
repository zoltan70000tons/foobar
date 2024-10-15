<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Models\Booking;


class BookingRepository implements BookingInterface
{
  function getAll()
  {
     return Booking::with(['cabin','cabin.cabinType','customer', 'customer.detail' ])->get();
  }

  function find($id)
  {
    return Cabin::find($id);
  }

  function save(array $data): ?Booking
  {
    return new Booking();
  }
  function update(array $data, $id)
  {
   
  }
  function delete($id) {}



  function addTags(array $tags, array $cabins) {}
}
