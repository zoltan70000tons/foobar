<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_tags')->insert([
            'name' => 'NEW',
            'description' => 'NEW',
            'color' => '#66bb6a',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_tags')->insert([
            'name' => 'STAFF',
            'description' => 'STAFF',
            'color' => '#1976d2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
