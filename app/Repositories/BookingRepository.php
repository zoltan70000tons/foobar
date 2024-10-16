<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Models\Booking;


class BookingRepository implements BookingInterface
{
  function getAll()
  {
    return Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail'])->get();
  }

  function getByTag($tags)
  {
      $query = Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail', 'passengers'])
          ->withSum('passengers as balance', 'passenger_balance')
          ->withSum('passengers as cost', 'passenger_allocated_cost');
  
      $query->where(function ($query) use ($tags) {
          foreach ($tags as $tag) {
              $query->orWhereJsonContains('tags', $tag);
          }
      });
  
      $results = $query->get();
      $results->each(function ($booking) {
          $booking->fullName = $booking->customer->detail->full_name ?? null;
          $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;
      });
      
      return $results;
  }

  function find($id)
  {
    return Cabin::find($id);
  }

  function save(array $data): ?Booking
  {
    return new Booking();
  }
  function update(array $data, $id) {}
  function delete($id) {}



  function addTags(array $tags, array $cabins) {}
}
