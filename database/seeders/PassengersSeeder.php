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
        $bookings = Booking::whereHas('cabin.cabinType', function ($query) {
            $query->where('id', 2);
        })
            ->with(['cabin.cabinType', 'cabin.cabinCategory'])
            ->get();

        $faker = Faker::create();

        foreach ($bookings as $booking) {
            if ($booking->cabin && $booking->customer) {
                $lead_passenger_id = $booking->customer->id;
                $customer = $booking->customer;
                $pricePerPerson = $booking->cabin->cabinCategory->price;
                Passenger::updateOrCreate([
                    'booking_id' => $booking->id,
                    'lead_passenger' => true,
                ], [
                    'confirmed_booking_email' => $faker->boolean,
                    'survivor_number' => $faker->randomNumber(),
                    'payment_method' => 'CREDIT_CARD',
                    'gender' => 'M',
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
                    'passenger_allocated_cost' => $pricePerPerson,
                    'passenger_balance' => $faker->randomFloat(2, 0, $pricePerPerson),
                    'was_on_board' => $faker->boolean,
                ]);
            }
        }


        // single female
        $bookings = Booking::whereHas('cabin.cabinType', function ($query) {
            $query->where('id', 3);
        })
            ->with(['cabin.cabinType', 'cabin.cabinCategory']) // Incluir tanto cabinType como cabinCategory
            ->get();

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
                    'payment_method' => 'CREDIT_CARD',
                    'gender' => 'F',
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
                    'passenger_allocated_cost' => $pricePerPerson,
                    'passenger_balance' => $faker->randomFloat(2, 0, $pricePerPerson),
                    'was_on_board' => $faker->boolean,
                ]);
            }
        }

        $bookings = Booking::whereHas('cabin.cabinType', function ($query) {
            $query->where('id', 1);
        })
            ->with(['cabin.cabinType', 'cabin.cabinCategory']) // Incluir tanto cabinType como cabinCategory
            ->get();

        

        foreach ($bookings as $booking) {
            $faker = Faker::create();
            if ($booking->cabin && $booking->customer) {
                $lead_passenger = $booking->customer;
                $capacity = $booking->cabin->cabinCategory->capacity;
                $pricePerPerson = $booking->cabin->cabinCategory->price;

                //add lead passenger
                        Passenger::updateOrCreate([
                            'booking_id' => $booking->id,
                            'lead_passenger' => true,
                        ], [
                            'confirmed_booking_email' => $faker->boolean,
                            'survivor_number' => $faker->randomNumber(),
                            'payment_method' => 'CREDIT_CARD',
                            'gender' => $lead_passenger->detail->gender,
                            'first_name' => $lead_passenger->detail->first_name,
                            'middle_name' => $lead_passenger->detail->middle_name,
                            'last_name' => $lead_passenger->detail->last_name,
                            'dob' => $lead_passenger->detail->dob,
                            'citizenship' => $lead_passenger->detail->citizenship,
                            'address_first' => $faker->streetAddress,
                            'city' => $faker->city,
                            'state' => $faker->state,
                            'postal_code' => $faker->postcode,
                            'country' => $faker->country,
                            'email' => $lead_passenger->email,
                            'phone' => $faker->phoneNumber,
                            'emergency_c_name' => $faker->name,
                            'emergency_c_phone' => $faker->phoneNumber,
                            'hear_about' => 'Internet',
                            'newsletter' => $faker->boolean,
                            'travel_info' => $faker->boolean,
                            'terms_n_cons' => true,
                            'cabin_conf_accp' => $faker->boolean,
                            'single_t_agreement' => $faker->boolean,
                            'passenger_allocated_cost' => $pricePerPerson,
                            'passenger_balance' => $faker->randomFloat(2, 1000, $pricePerPerson),
                            'was_on_board' => $faker->boolean,
                        ]);
                // get random passengers to complete booking
                   for($i = 0; $i < $capacity; $i++){
                    $faker = Faker::create();
                    Passenger::updateOrCreate([
                        'booking_id' => $booking->id,
                        'lead_passenger' => false,
                    ], [
                        'confirmed_booking_email' => $faker->boolean,
                        'survivor_number' => $faker->randomNumber(),
                        'payment_method' => 'CREDIT_CARD',
                        'gender' => $faker->randomElement(['M', 'F']),
                        'first_name' => $faker->firstName,
                        'middle_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'dob' => $lead_passenger->detail->dob,
                        'citizenship' =>  $faker->country,
                        'address_first' => $faker->streetAddress,
                        'city' => $faker->city,
                        'state' => $faker->state,
                        'postal_code' => $faker->postcode,
                        'country' => $faker->country,
                        'email' => $faker->email,
                        'phone' => $faker->phoneNumber,
                        'emergency_c_name' => $faker->name,
                        'emergency_c_phone' => $faker->phoneNumber,
                        'hear_about' => 'Internet',
                        'newsletter' => $faker->boolean,
                        'travel_info' => $faker->boolean,
                        'terms_n_cons' => true,
                        'cabin_conf_accp' => $faker->boolean,
                        'single_t_agreement' => $faker->boolean,
                        'passenger_allocated_cost' => $pricePerPerson,
                        'passenger_balance' => $faker->randomFloat(2, 0, $pricePerPerson),
                        'was_on_board' => $faker->boolean,
                    ]);
                   } 


                // dd($booking->cabin->cabinCategory);
            }
        }
    }
}
