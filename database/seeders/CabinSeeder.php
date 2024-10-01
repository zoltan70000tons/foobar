<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabin;
use App\Models\CabinCategory;
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

      // Find the corresponding cabin_category_id based on category_code and capacity
      $cabinCategoryId = CabinCategory::where('category_code', $record['Cat'])
        ->where('capacity', $record['Capacity'])
        ->first()
        ->id;

      // Convert "Y" and "N" to boolean for balcony and obstrucuted_view fields
      $balcony = $record['Balcony'] === 'Y';
      $obstructedView = $record['Obstructed View'] === 'Y';
      $accessible = $record['Accessible'] === 'Y';

      // Find the connects_with cabin ID based on cabin_number
      $connectingCabin = Cabin::where('cabin_number', $record['Connects with'])->first();
      $connectingCabinId = $connectingCabin ? $connectingCabin->id : null;

      // Prepare data array
      $cabinData = [
        'cabin_category_id' => $cabinCategoryId,
        'cabin_type_id' => $record['Category Type'], // Assuming the cabin_type is always 1, meaning private cabin
        'cabin_number' => $record['Cabin #'],
        'deck' => $record['Deck'],
        'total_berths' => $record['Total Berths'],
        'lower_bed_type_1' => $record['Lower Bed Type1'],
        'lower_bed_type_2' => $record['Lower Bed Type2'],
        'upper_berths' => $record['Upper Berths'],
        'accessible' => $accessible,
        'connects_with' => $connectingCabinId,
        'location' => $record['Location'],
        'balcony' => $balcony,
        'obstructed_view' => $obstructedView,
        'inventory' => $record['Inventory'],
        'notes' => $record['Notes'],
        'tags' => $record['Tags'], 
        'status' => $record['Status'],
      ];

      // Create the Cabin entry
      Cabin::create($cabinData);
    }
  }
}
