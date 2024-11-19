<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddonSeeder extends Seeder
{
  public function run()
  {
    DB::table('addons')->insert([
      [
        'code' => 'CHOOSE_YOUR_CABIN',
        'type' => 'FIXED',
        'value' => 100.00,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'TAXES',
        'type' => 'FIXED',
        'value' => 487.00,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_I',
        'type' => 'FIXED',
        'value' => 42.80,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_O',
        'type' => 'FIXED',
        'value' => 42.80,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_B',
        'type' => 'FIXED',
        'value' => 44.55,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
      [
        'code' => 'CARBON_OFFSET_S',
        'type' => 'FIXED',
        'value' => 56.90,
        'event_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
      ],
    ]);
  }
}
