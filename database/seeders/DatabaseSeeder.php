<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Cabin;
use Illuminate\Database\Seeder;
use Mockery\Generator\StringManipulation\Pass\Pass;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            RolesSeeder::class,
            UserSeeder::class,
            MembershipTypeSeeder::class,
            EventSeeder::class,
            MembershipSeeder::class,
            PresalePeriodSeeder::class,
            CruiseSeeder::class,
            CabinCategorySeeder::class,
            CabinTypeSeeder::class,
            CabinSeeder::class,
            //BookingSeeder::class,
            //PassengersSeeder::class,
            AdjustmentsSeeder::class,
            //PaymentSeeder::class,
            EmailTemplatesSeeder::class,  
            CustomerBookingSeeder::class,
            UserTagsSeeder::class,
        ]);
    }
}
