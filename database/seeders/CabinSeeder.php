<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabin;
use App\Models\CabinSpec;
use App\Models\CabinCategory;
use App\Models\CabinCategorySpec;
use League\Csv\Reader;

class CabinSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Path to the CSV file
    $csvFilePath = database_path('seeders/data/cabins.csv');

    // Reading the CSV file
    $csv = Reader::createFromPath($csvFilePath, 'r');
    $csv->setHeaderOffset(0); // Assumes the first row contains the header

    // Iterate through each record in the CSV
    foreach ($csv as $record) {
      // Find the corresponding CabinCategorySpec based on category_code and capacity
      $cabinCategorySpec = CabinCategorySpec::where('category_code', $record['Cat'])
        ->where('capacity', $record['Capacity'])
        ->first();

      if (!$cabinCategorySpec) {
        // Skip this cabin if no matching spec is found
        continue;
      }

      // Find the CabinCategory that matches the CabinCategorySpec
      $cabinCategory = CabinCategory::where('cabin_category_spec_id', $cabinCategorySpec->id)->first();

      if (!$cabinCategory) {
        // Skip this cabin if no matching category is found
        continue;
      }

      // Convert "Y" and "N" to boolean for static fields
      $balcony = $record['Balcony'] === 'Y';
      $obstructedView = $record['Obstructed View'] === 'Y';
      $accessible = $record['Accessible'] === 'Y';

      // Check if a CabinSpec already exists with the same static data
      $cabinSpec = CabinSpec::firstOrCreate([
        'cabin_number' => $record['Cabin #'],
      ], [
        'deck' => $record['Deck'],
        'total_berths' => $record['Total Berths'],
        'lower_bed_type_1' => $record['Lower Bed Type1'],
        'lower_bed_type_2' => $record['Lower Bed Type2'],
        'upper_berths' => $record['Upper Berths'],
        'accessible' => $accessible,
        'connects_with' => $record['Connects with'] ? CabinSpec::where('cabin_number', $record['Connects with'])->first()?->id : null,
        'location' => $record['Location'],
        'balcony' => $balcony,
        'obstructed_view' => $obstructedView,
      ]);

      // Prepare the dynamic data for the Cabin
      $cabinData = [
        'cabin_category_id' => $cabinCategory->id,
        'cabin_type_id' => $record['Category Type'],
        'cabin_spec_id' => $cabinSpec->id,
        'inventory' => $record['Inventory'],
        'notes' => $record['Notes'],
        'internal_notes' => $record['Internal Notes'] ?? "",
        'tags' => $record['Tags'],
        'status' => $record['Status'],
      ];

      // Create the Cabin entry
      Cabin::create($cabinData);
    }
  }
}
