<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\CabinCategorySeeder;

class SeedCabinCategories extends Command {
    // Use an option instead of a required argument
    protected $signature = 'seed:cabin-categories {--event_id=}';
    protected $description = 'Seed cabin categories with a specific event ID';

    public function handle() {
        $eventId = $this->option('event_id');

        if (!$eventId) {
            $this->error('Please provide an --event_id option.');
            return;
        }

        // Optional: Validate if the Event exists
        if (!\App\Models\Event::find($eventId)) {
            $this->error("Event with ID {$eventId} does not exist.");
            return;
        }

        // Run the seeder and pass the event ID
        $seeder = new CabinCategorySeeder();
        $seeder->run($eventId);

        $this->info("Cabin categories seeded for event ID: {$eventId}");
    }
}
