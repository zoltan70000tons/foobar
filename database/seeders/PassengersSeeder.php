<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Passenger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PassengersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // single male
        $bookings =$bookings = Booking::whereHas('cabin.cabinType', function ($query) {
            $query->where('id', 2);
        })->get();

        $faker = Faker::create();

        foreach ($bookings as $booking) {
            if ($booking->cabin && $booking->customer) {
                $lead_passenger_id = $booking->customer->id;
                $customer = $booking->customer;
                Passenger::updateOrCreate([
                    'booking_id' => $booking->id,
                    'lead_passenger' => true,
                ], [
                    'confirmed_booking_email' => $faker->boolean,
                    'survivor_number' => $faker->randomNumber(),
                    'payment_method' => 'credit_card', 
                    'gender' => 'Male',
                    'first_name' => $booking->customer->detail->first_name,
                    'middle_name' => $booking->customer->detail->middle_name,
                    'last_name' => $booking->customer->detail->last_name,
                    'dob' => $booking->customer->detail->dob,
                    'citizenship' => $booking->customer->detail->citizenship,
                    'address_first' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'postal_code' => $faker->postcode,
                    'country' => $faker->country,
                    'email' => $booking->customer->email,
                    'phone' => $faker->phoneNumber,
                    'emergency_c_name' => $faker->name,
                    'emergency_c_phone' => $faker->phoneNumber,
                    'hear_about' => 'Internet',
                    'newsletter' => $faker->boolean,
                    'travel_info' => $faker->boolean,
                    'terms_n_cons' => true,
                    'cabin_conf_accp' => $faker->boolean,
                    'single_t_agreement' => $faker->boolean,
                    'passenger_allocated_cost' => $faker->randomFloat(2, 1000, 5000),
                    'passenger_balance' => $faker->randomFloat(2, 0, 2000),
                    'was_on_board' => $faker->boolean,
                ]);
            }
        }


         // single female
         $bookings =$bookings = Booking::whereHas('cabin.cabinType', function ($query) {
            $query->where('id', 3);
        })->get();

        $faker = Faker::create();

        foreach ($bookings as $booking) {
            if ($booking->cabin && $booking->customer) {
                $lead_passenger_id = $booking->customer->id;
                $customer = $booking->customer;
                //dd($booking->customer->detail);
                Passenger::updateOrCreate([
                    'booking_id' => $booking->id,
                    'lead_passenger' => true,
                ], [
                    'confirmed_booking_email' => $faker->boolean,
                    'survivor_number' => $faker->randomNumber(),
                    'payment_method' => 'credit_card', 
                    'gender' => 'Female',
                    'first_name' => $booking->customer->detail->first_name,
                    'middle_name' => $booking->customer->detail->middle_name,
                    'last_name' => $booking->customer->detail->last_name,
                    'dob' => $booking->customer->detail->dob,
                    'citizenship' => $booking->customer->detail->citizenship,
                    'address_first' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'postal_code' => $faker->postcode,
                    'country' => $faker->country,
                    'email' => $booking->customer->email,
                    'phone' => $faker->phoneNumber,
                    'emergency_c_name' => $faker->name,
                    'emergency_c_phone' => $faker->phoneNumber,
                    'hear_about' => 'Internet',
                    'newsletter' => $faker->boolean,
                    'travel_info' => $faker->boolean,
                    'terms_n_cons' => true,
                    'cabin_conf_accp' => $faker->boolean,
                    'single_t_agreement' => $faker->boolean,
                    'passenger_allocated_cost' => $faker->randomFloat(2, 1000, 5000),
                    'passenger_balance' => $faker->randomFloat(2, 0, 2000),
                    'was_on_board' => $faker->boolean,
                ]);
            }
        }

        
    }
}
