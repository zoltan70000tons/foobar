<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
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
            // Convert the gallery column to a JSON array
            $images = !empty($record['gallery']) ? explode(',', $record['gallery']) : null;

            // Determine the category number based on category_type
            $categoryNumber = null;
            // Determine the category number based on category_type
            switch ($record['category_type']) {
              case 'Interior':
                $categoryNumber = '1';
                break;
              case 'Ocean View':
                $categoryNumber = '2';
                break;
              case 'Balcony':
                $categoryNumber = '3';
                break;
              case 'Suite':
                $categoryNumber = '4';
                break;
              default:
                $categoryNumber = null; // Handle unexpected category types
                break;
            }
            

            // Prepare the category description array for each language
            $categoryDescription = [
                'en' => $record['en_description'],
                'es' => $record['es_description'],
                'de' => $record['de_description'],
            ];

            // Create or retrieve the CabinCategorySpec
            $specData = [
                'category_type'   => $record['category_type'],
                'category_code'   => $record['category_code'],
                'category_name'   => $record['category_name'],
                'capacity'        => $record['capacity'],
                'description'     => $categoryDescription, // JSONB field
                'images'          => $images, // JSON field
                'iframe'          => $record['vr'],
                'decks'           => $record['decks'],
                'display_order'   => $record['display_order'],
                'cruise_id'       => $cruiseId,
                'category_number' => $categoryNumber,
            ];

            $cabinCategorySpec = CabinCategorySpec::firstOrCreate([
                'category_code' => $record['category_code'],
                'capacity' => $record['capacity'],
            ], $specData);

            // Create the CabinCategory with the associated spec and event
            CabinCategory::create([
                'price'                   => $record['price'],
                'cabin_category_spec_id'  => $cabinCategorySpec->id,
                'event_id'                => $eventId,
            ]);
        }
    }
}