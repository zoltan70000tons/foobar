<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdjustmentsSeeder extends Seeder
{
  public function run()
  {
    DB::statement('TRUNCATE TABLE adjustments RESTART IDENTITY CASCADE;');

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
        'system' => true,
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
        'system' => true,
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
        'system' => true,
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
        'system' => true,
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
        'system' => true,
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
        'system' => true,
      ],
      [
        'code' => 'MEMBERSHIP_BLACK',
        'type' => 'DISCOUNT',
        'operation' => 'PERCENTAGE',
        'value' => 10.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      // Addons
      [
        'code' => 'CHOOSE_YOUR_CABIN',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 100.0,
        'restrictions' => json_encode([
          'logic' => 'OR',
          'conditions' => [
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => "Owner's Suite",
            ],
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => 'Grand Suite - 1 Bedroom',
            ],
            [
              'model' => 'cabin',
              'property' => 'category_name',
              'operator' => 'equals',
              'value' => 'Grand Suite - 2 Bedroom',
            ],
          ],
          'behavior' => 'APPLY_ONLY_IF_MATCHED',
        ]),
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      [
        'code' => 'CARBON_OFFSET_I',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 37.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      [
        'code' => 'CARBON_OFFSET_O',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 37.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      [
        'code' => 'CARBON_OFFSET_B',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 38.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      [
        'code' => 'CARBON_OFFSET_S',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 49.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
      [
        'code' => 'TAX',
        'type' => 'ADDON',
        'operation' => 'FIXED',
        'value' => 494.0,
        'restrictions' => null,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'system' => true,
      ],
    ]);
  }
}
