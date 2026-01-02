<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Passenger>
 */
class PassengerFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'booking_id' => fake()->name,
            'confirmed_booking_email' => false,
            'lead_passenger' => true,
            'passenger_order' => 1,
            'survivor_number' => fake()->numberBetween(100000000, 999999999),
            'payment_method' => fake()->randomElement(['CREDIT_CARD', 'BANK_TRANSFER']),
            'gender' => fake()->date(),
            'first_name' => strtoupper(fake()->firstName),
            'middle_name' => null,
            'last_name' => strtoupper(fake()->lastName),
            'dob' => fake()->word,
            'citizenship' => fake()->randomElement(array_keys(\Symfony\Component\Intl\Countries::getNames())),
            'address_first' => fake()->streetAddress,
            'address_second' => null,
            'city' => fake()->city,
            'state' => null,
            'postal_code' => fake()->postcode,
            'country' => fake()->country,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'emergency_c_name' => fake()->name,
            'emergency_c_phone' => fake()->phoneNumber,
            'special_request' => fake()->url,
            'special_options' => fake()->url,
            'hear_about' => fake()->url,
            'referral_details' => fake()->url,
            'newsletter' => fake()->boolean(51),
            'travel_info' => fake()->boolean(51),
            'terms_n_cons' => fake()->boolean(100),
            'empty_seat' => fake()->boolean(0),
            'cabin_conf_accp' => fake()->boolean(50),
            'single_t_agreement' => fake()->boolean(90),
            'passenger_allocated_cost' => fake()->url,
            'passenger_balance' => fake()->url,
            'was_on_board' => false,
            'language' => fake()->randomElement(['en', 'de', 'es']),
        ];
    }
}
