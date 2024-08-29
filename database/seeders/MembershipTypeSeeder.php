<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
 
class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Black Survivors
        DB::table('membership_types')->insert([
            'name' => 'Black',
            'booking_number_requirement' => '10',
            'discount_value' => '10',
            'created_at' => Carbon::now(),
        ]);
        
        // Gold+ Survivors
        DB::table('membership_types')->insert([
            'name' => 'Gold+',
            'booking_number_requirement' => '9',
            'discount_value' => '10',
            'created_at' => Carbon::now(),
        ]);
        
        // Gold Survivors
        DB::table('membership_types')->insert([
            'name' => 'Gold',
            'booking_number_requirement' => '3',
            'discount_value' => '10',
            'created_at' => Carbon::now(),
        ]);
        
        // Silver+ Survivors
        DB::table('membership_types')->insert([
            'name' => 'Silver+',
            'booking_number_requirement' => '2',
            'discount_value' => '5',
            'created_at' => Carbon::now(),
        ]);
        
        // Silver Survivors
        DB::table('membership_types')->insert([
            'name' => 'Silver',
            'booking_number_requirement' => '1',
            'discount_value' => '5',
            'created_at' => Carbon::now(),
        ]);
    }
}