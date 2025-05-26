<?php

namespace App\Console\Commands;

use App\Helpers\CustomerHelper;
use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\CustomerAddress;
use App\Models\SurvivorNumber;
use App\Models\UserTag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SeedTestUsers extends Command
{
    protected $signature = 'seed:test-users {--membership=} {--count=5}';
    protected $description = 'Generate test users by membership tier for customer service testing';

    public function handle()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $faker = Faker::create();
        $membership = strtolower($this->option('membership') ?? 'none');
        $count = (int) $this->option('count');

        $headers = ['Name', 'LastName', 'Email', 'Username', 'Membership', 'Survivor #'];
        $rows = [];

        $csvContent = implode(',', $headers) . "\n";

        $membershipTypeName = ucfirst(strtolower($this->option('membership')));
        $membershipType = \App\Models\MembershipType::where('name', $membershipTypeName)->first();

        if (!$membershipType) {
            $this->error("Error, '$membershipTypeName' not found.");
            return;
        }

        for ($i = 1; $i <= $count; $i++) {

            $email = '';
            $username = '';
            $attempt = 1;
            do {
                $email = "booking+test_user_{$membership}_{$attempt}@70000tons.com";
                $username = "testuser_{$membership}_{$attempt}";
                $exists = User::where('email', $email)->exists();
                $attempt++;
            } while ($exists);

            $user = User::create([
                'email' => $email,
                'username' => $username,
                'password' => Hash::make('password'),
                'organization_id' => env('ORGANIZATION_ID', 1),
            ]);

            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'gender' => $faker->randomElement(['M', 'F']),
                'first_name' => strtoupper($faker->firstName),
                'middle_name' => strtoupper($faker->firstName),
                'last_name' => strtoupper($faker->lastName),
                'dob' => $faker->date(),
                'citizenship' => $faker->countryISOAlpha3(),
                'phone' => $faker->e164PhoneNumber(),
                'avatar' => $faker->imageUrl(),
                'emergency_c_name' => $faker->name,
                'emergency_c_phone' => $faker->e164PhoneNumber(),
                'language' => $faker->randomElement(['es', 'de', 'en']),
            ]);

            CustomerAddress::create([
                'user_id' => $user->id,
                'address_first' => $faker->streetAddress,
                'address_second' => $faker->secondaryAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'postal_code' => $faker->postcode,
                'country' => $faker->countryISOAlpha3(),
            ]);
            $survivorNumber = CustomerHelper::generateSurvivorNumber($user->id);
            SurvivorNumber::create([
                'user_id' => $user->id,
                'survivor_number' => $survivorNumber,
            ]);

            $user->membership()->create([
                'user_id' => $user->id,
                'membership_id' => $membershipType->id,
            ]);

            $tag = UserTag::firstOrCreate(
                ['name' => 'TEST'],
                [
                    'description' => 'TEST',
                    'color' => '#ff9800',
                ]
            );
            $user->tags()->attach($tag->id);

            setPermissionsTeamId(1);
            $user->assignRole('Customer');

            $rows[] = [$userDetail->first_name, $userDetail->last_name, $email, $username, ucfirst($membership), $survivorNumber];
            $csvContent .= implode(',', [$userDetail->first_name, $userDetail->last_name, $email, $username, $membership, $survivorNumber]) . "\n";
        }

        $this->table($headers, $rows);
        $filename = 'test_users_' . strtolower($membership) . '_' . now()->format('Y-m-d_Hi') . '.csv';
        Storage::disk('local')->put($filename, $csvContent);
        $this->info('✔ Test users generated and saved to storage/app/test_users.csv');
    }
}
