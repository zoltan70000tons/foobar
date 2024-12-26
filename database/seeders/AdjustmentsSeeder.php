<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdjustmentsSeeder extends Seeder
{
  public function run()
  {
    DB::table('adjustments')->insert([
      // Discounts
      [
        'code' => 'SINGLE_TICKET_FEE',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 100.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'PAID_IN_FULL',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      // Membership discounts
      [
        'code' => 'MEMBERSHIP_SILVER',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'MEMBERSHIP_SILVER_PLUS',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 5.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'MEMBERSHIP_GOLD',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'MEMBERSHIP_GOLD_PLUS',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'BLACK',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      // Addons
      [
        'code' => 'CHOOSE_YOUR_CABIN',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 100.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_I',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 42.8,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_O',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 42.8,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_B',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 44.55,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_S',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 56.9,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'TAX',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 487.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }
}
