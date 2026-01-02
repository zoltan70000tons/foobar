<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CabinTypeSeeder extends Seeder {
    /**
     * Run the database seeders.
     */
    public function run(): void {
        // Private Cabin
        DB::table('cabin_types')->insert([
            'cabin_type' => 'Private Cabin',
            'created_at' => Carbon::now(),
        ]);

        // Single Male
        DB::table('cabin_types')->insert([
            'cabin_type' => 'Single Male',
            'created_at' => Carbon::now(),
        ]);

        // Single Female
        DB::table('cabin_types')->insert([
            'cabin_type' => 'Single Female',
            'created_at' => Carbon::now(),
        ]);
    }
}
