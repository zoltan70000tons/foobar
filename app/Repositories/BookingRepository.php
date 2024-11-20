<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Models\Booking;
use DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Models\BookingLog;

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


  function getByStatus($status, $keyword = null)
  {
    $query = Booking::with(['cabin', 'cabin.cabinType', 'customer', 'customer.detail', 'passengers', 'agent', 'agent.detail'])
      ->withSum('passengers as balance', 'passenger_balance')
      ->withSum('passengers as cost', 'passenger_allocated_cost')
      ->where('status', '=', $status);

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
    return Booking::find($id);
  }

  function findByCode($code)
  {
    return Booking::with('cabin', 'cabin.cabinType', 'cabin.cabinCategory', 'passengers', 'logs', 'logs.user', 'lockedBy')->where('booking_code', '=', $code)->first();
  }

  function save(array $data): ?Booking
  {
    return new Booking();
  }

  function update(array $data, $id)
  {
    $booking = $this->find($id);
    $tags = $data['tags'];
    $booking->tags = $tags;
    $booking->save();
  }

  function delete($id) {}

  function assignAgent($code, $user)
  {
    Log::info($code);
    Log::info($user);
    dd('ok');
  }

  function addTags(array $tags, array $cabins) {}

  function changeCabin(Booking $booking, $cabin_number)
  {
    return $booking->changeCabin($cabin_number);
  }

  function changeCode(Booking $booking, $new_code)
  {
    try {
      if (empty($new_code)) {
        throw new InvalidArgumentException('The new booking code cannot be empty.');
      }

      if (Booking::where('booking_code', $new_code)->exists()) {
        throw new InvalidArgumentException('The new booking code is already in use.');
      }
      $original_code = $booking->booking_code;
      $booking->booking_code = $new_code;
      $booking->save();
      $blog = new BookingLog();
      $blog->booking_id = $booking->id;
      $blog->user_id = auth()->id();
      $blog->action = 'Changed booking code';
      $blog->description = "Booking code changed from {$original_code} to {$new_code}.";
      $blog->save();

      return $booking;
    } catch (\Exception $e) {
      return false;
    }
  }
}
