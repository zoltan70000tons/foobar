<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Container\Container;
use Faker\Generator;
use App\Models\User;
use App\Models\CustomerAddress;

class CustomerAddressSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get all users with the 'Customer' role
        $customers = User::role('Customer')->get();

        // Seed addresses for each customer
        foreach ($customers as $customer) {
            CustomerAddress::updateOrCreate(
                ['user_id' => $customer->id],
                [
                    'address_first' => $this->faker->streetAddress,
                    'address_second' => $this->faker->secondaryAddress,
                    'city' => $this->faker->city,
                    'state' => $this->faker->state,
                    'postal_code' => $this->faker->postcode,
                    'country' => $this->faker->countryCode, 
                ]
            );
        }
    }
}
