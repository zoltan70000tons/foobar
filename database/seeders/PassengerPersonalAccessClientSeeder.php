<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use RuntimeException;

class PassengerPersonalAccessClientSeeder extends Seeder {
    public function run(): void {
        /** @var ClientRepository $clients */
        $clients = app(ClientRepository::class);

        // Ensure a personal access client exists for the 'passengers'
        try {
            $clients->personalAccessClient('passengers');
        } catch (RuntimeException $e) {
            $clients->createPersonalAccessGrantClient('Passengers Personal Access Client', 'passengers');
        }
    }
}
