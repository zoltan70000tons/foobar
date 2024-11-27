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

        // Map for category_code
        $categoryMapping = [
            1 => ['4VL', '4VH', '3V', '2V', '1V', '2T', '1R', '1Q'],
            2 => ['4N', '3N', '2N', '1N', '4M', '3M', '1K', '1L'],
            3 => ['5D', '4D', '2D', '1D', '4B', '3B', '2B', '1B'],
            4 => ['VP'],
            5 => ['J4', 'J3', 'GS', 'G3', 'OS', 'GT'],
        ];

        // Iterate through each record in the CSV
        foreach ($csv as $record) {
            // Convert the gallery column to a JSON array
            $images = !empty($record['gallery']) ? explode(',', $record['gallery']) : null;
            
            // Determine the category number based on category_code
            $categoryNumber = null;
            foreach ($categoryMapping as $number => $codes) {
                if (in_array($record['category_code'], $codes)) {
                    $categoryNumber = $number;
                    break;
                }
            }
            
            // Prepare the category description array for each language
            $categoryDescription = [
              'en' => $record['en_description'],
              'es' => $record['es_description'],
              'de' => $record['de_description'],
            ];

            // Prepare data array
            $categoryData = [
                'category_type'   => $record['category_type'],
                'category_code'   => $record['category_code'],
                'category_name'   => $record['category_name'],
                'capacity'        => $record['capacity'],
                'description'     => json_encode($categoryDescription),
                'images'          => $images,
                'iframe'          => $record['vr'],
                'price'           => $record['price'],
                'decks'           => $record['decks'],
                'display_order'   => $record['display_order'],
                'cruise_id'       => $cruiseId, // Use the dynamically retrieved cruise_id
                'event_id'        => $eventId, // Use the dynamically retrieved event_id
                'category_number' => $categoryNumber, // Add the determined category number
            ];

            // Create the CabinCategory
            CabinCategory::create($categoryData);
        }
    }
}
