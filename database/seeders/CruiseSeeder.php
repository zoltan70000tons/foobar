<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CruiseSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('cruises')->insert([
            'name' => 'Freedom of the Seas',
        ]);

        DB::table('cruises')->insert([
            'name' => 'Independence of the Seas',
        ]);
    }
}
