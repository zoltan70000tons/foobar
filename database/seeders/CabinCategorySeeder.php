<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CabinCategory;
use App\Models\Cruise;
use App\Models\Event;
use League\Csv\Reader;

class CabinCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve the first Event and Cruise
        $eventId = Event::first()->id;
        $cruiseId = Cruise::first()->id;

        // Path to the CSV file
        $csvFilePath = database_path('seeders/data/cabin_categories.csv');

        // Reading the CSV file
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(0); // Assumes the first row contains the header

        // Iterate through each record in the CSV
        foreach ($csv as $record) {
            // Prepare data array
            $categoryData = [
                'category_type'   => $record['category_type'],
                'category_code'   => $record['category_code'],
                'category_name'   => $record['category_name'],
                'capacity'        => $record['capacity'],
                'description'     => null, // We might need to update this field to accomodate the translation, or have it as a JSON field
                'images'          => null, // Default to null; can be updated later
                'iframe'          => null, // Default to null; can be updated later
                'price'           => $record['price'],
                'decks'           => $record['decks'],
                'display_order'   => $record['display_order'],
                'cruise_id'       => $cruiseId, // Use the dynamically retrieved cruise_id
                'event_id'        => $eventId, // Use the dynamically retrieved event_id
            ];

            // Create the CabinCategory
            CabinCategory::create($categoryData);
        }
    }
}
