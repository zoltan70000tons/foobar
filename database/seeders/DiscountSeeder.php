<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
  public function run()
  {
    DB::table('discounts')->insert([
      [
        'code' => 'SINGLE_TICKET_FEE',
        'restrictions' => null,
        'type' => 'FIXED',
        'value' => 100.00,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'PAID_IN_FULL',
        'restrictions' => null,
        'type' => 'PERCENTAGE',
        'value' => 5.00,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }
}
