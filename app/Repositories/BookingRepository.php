<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Models\Booking;
use DB;

class BookingRepository implements BookingInterface
{
  function getAll()
  {
    return Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail'])->get();
  }

  function getByTag($tags, $keyword = null)
  {
    $query = Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail', 'passengers'])
      ->withSum('passengers as balance', 'passenger_balance')
      ->withSum('passengers as cost', 'passenger_allocated_cost');

      if (!empty($tags)) {
        $query->where(function ($query) use ($tags) {
            foreach ($tags as $tag) {
                $query->orWhereRaw(
                    "EXISTS (SELECT 1 FROM jsonb_array_elements_text(bookings.tags) as t WHERE LOWER(t) ILIKE ?)", 
                    ['%' . strtolower($tag) . '%']
                );
            }
        });
    }
    
    

    if (!empty($keyword)) {
      $keyword = strtolower($keyword);

      $query->where(function ($query) use ($keyword) {
        $query->orWhere(DB::raw('LOWER(booking_code)'), 'like', '%' . $keyword . '%');

        $query->orWhereHas('customer.detail', function ($query) use ($keyword) {
          $query->where(function ($query) use ($keyword) {
            $query->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
              ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
              ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
          });
        });

        $query->orWhereHas('cabin.cabinType', function ($query) use ($keyword) {
          $query->where(DB::raw('LOWER(cabin_type)'), 'like', '%' . $keyword . '%');
        });

        $query->orWhereHas('passengers', function ($query) use ($keyword) {
          $query->where(function ($query) use ($keyword) {
            $query->where(DB::raw('LOWER(first_name)'), 'like', '%' . $keyword . '%')
              ->orWhere(DB::raw('LOWER(last_name)'), 'like', '%' . $keyword . '%')
              ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', '%' . $keyword . '%');
          });
        });
      });
    }

    $results = $query->get();
    $results->each(function ($booking) {
      $booking->fullName = $booking->customer->detail->full_name ?? null;
      $booking->cabinType = $booking->cabin->cabinType->cabin_type ?? null;
      $booking->subRows = $booking->passengers ?? [];
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
