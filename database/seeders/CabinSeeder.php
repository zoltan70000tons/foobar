<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Cabin;
use App\Models\Bed;

class CabinSeeder extends Seeder
{
    public function run()
    {
        $cabins = [
// INTERIOR
// INTERIOR - STANDARD INTERIOR
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3', 'price' => 1799, 'code' => '4V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6 - 12', 'price' => 1933, 'code' => '4V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 6 - 10', 'price' => 2066, 'code' => '3V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3, 6 - 10', 'price' => 2199, 'code' => '2V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3, 9, 10', 'price' => 2333, 'code' => '1V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Triple', 'capacity' => 3, 'deck' => '2, 6 - 10', 'price' => 1666, 'code' => '3V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Triple', 'capacity' => 3, 'deck' => '2, 3, 9, 10', 'price' => 1933, 'code' => '1V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2, 6 - 10', 'price' => 1266, 'code' => '3V'],
['category' => 'INTERIOR', 'subcategory' => 'STANDARD INTERIOR', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2, 3, 9, 10', 'price' => 1533, 'code' => '1V'],
// INTERIOR - PROMENADE VIEW
['category' => 'INTERIOR', 'subcategory' => 'PROMENADE VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6 - 8', 'price' => 2333, 'code' => '2T'],
// INTERIOR - SPACIOUS INTERIOR
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS INTERIOR', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2', 'price' => 2066, 'code' => '1R'],
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS INTERIOR', 'type' => 'Quint', 'capacity' => 5, 'deck' => '2', 'price' => 1766, 'code' => '1R'],
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS INTERIOR', 'type' => 'Sextuple', 'capacity' => 6, 'deck' => '2', 'price' => 1466, 'code' => '1R'],
// INTERIOR - SPACIOUS PROMENADE VIEW
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS PROMENADE VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '7, 8', 'price' => 2266, 'code' => '1Q'],
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS PROMENADE VIEW', 'type' => 'Quint', 'capacity' => 5, 'deck' => '7, 8', 'price' => 1966, 'code' => '1Q'],
['category' => 'INTERIOR', 'subcategory' => 'SPACIOUS PROMENADE VIEW', 'type' => 'Sextuple', 'capacity' => 6, 'deck' => '7, 8', 'price' => 1666, 'code' => '1Q'],
    //CHECKED
// OCEAN VIEW
// OCEAN VIEW -  STANDARD OCEAN VIEW
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3, 6, 7, 9', 'price' => 2099, 'code' => '4N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3', 'price' => 2233, 'code' => '3N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3, 7', 'price' => 2366, 'code' => '2N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '2, 3', 'price' => 2499, 'code' => '1N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Triple', 'capacity' => 3, 'deck' => '2, 3', 'price' => 1833, 'code' => '3N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Triple', 'capacity' => 3, 'deck' => '2, 3', 'price' => 2099, 'code' => '1N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2, 3', 'price' => 1433, 'code' => '3N'],
['category' => 'OCEAN VIEW', 'subcategory' => 'STANDARD OCEAN VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2, 3', 'price' => 1699, 'code' => '1N'],
// OCEAN VIEW - SPACIOUS OCEAN VIEW
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6, 7, 9, 10', 'price' => 2633, 'code' => '4M'],
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6, 8, 9', 'price' => 2766, 'code' => '3M'],
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS OCEAN VIEW', 'type' => 'Triple', 'capacity' => 3, 'deck' => '6, 8, 9', 'price' => 2366, 'code' => '3M'],
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS OCEAN VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '6, 8, 9', 'price' => 1966, 'code' => '3M'],
// OCEAN VIEW - ULTRA SPACIOUS OCEAN VIEW
['category' => 'OCEAN VIEW', 'subcategory' => 'ULTRA SPACIOUS OCEAN VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '2, 6 - 9', 'price' => 2499, 'code' => '1K'],
['category' => 'OCEAN VIEW', 'subcategory' => 'ULTRA SPACIOUS OCEAN VIEW', 'type' => 'Quint', 'capacity' => 5, 'deck' => '2, 6 - 9', 'price' => 2166, 'code' => '1K'],
['category' => 'OCEAN VIEW', 'subcategory' => 'ULTRA SPACIOUS OCEAN VIEW', 'type' => 'Sextuple', 'capacity' => 6, 'deck' => '2, 6 - 9', 'price' => 1733, 'code' => '1K'],
// OCEAN VIEW - SPACIOUS PANORAMIC OCEAN VIEW
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS PANORAMIC OCEAN VIEW', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '11, 12', 'price' => 2766, 'code' => '1L'],
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS PANORAMIC OCEAN VIEW', 'type' => 'Triple', 'capacity' => 3, 'deck' => '11, 12', 'price' => 2366, 'code' => '1L'],
['category' => 'OCEAN VIEW', 'subcategory' => 'SPACIOUS PANORAMIC OCEAN VIEW', 'type' => 'Quad', 'capacity' => 4, 'deck' => '11, 12', 'price' => 1966, 'code' => '1L'],
    //CHECKED
// BALCONY
// BALCONY - STANDARD BALCONY
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6-8', 'price' => 2299, 'code' => '5D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '8-10', 'price' => 2433, 'code' => '4D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6-8', 'price' => 2566, 'code' => '2D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6, 7', 'price' => 2699, 'code' => '1D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Triple', 'capacity' => 3, 'deck' => '6-8', 'price' => 1899, 'code' => '5D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Triple', 'capacity' => 3, 'deck' => '6-8', 'price' => 2166, 'code' => '2D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Triple', 'capacity' => 3, 'deck' => '6, 7', 'price' => 2266, 'code' => '1D'],
['category' => 'BALCONY', 'subcategory' => 'STANDARD BALCONY', 'type' => 'Quad', 'capacity' => 4, 'deck' => '6, 7', 'price' => 1833, 'code' => '1D'],

// BALCONY - SPACIOUS BALCONY
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6-10', 'price' => 2566, 'code' => '4B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6-10', 'price' => 2699, 'code' => '3B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '6-10', 'price' => 2833, 'code' => '2B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '9, 10', 'price' => 2966, 'code' => '1B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Triple', 'capacity' => 3, 'deck' => '6-10', 'price' => 2266, 'code' => '3B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Triple', 'capacity' => 3, 'deck' => '9, 10', 'price' => 2499, 'code' => '1B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Quad', 'capacity' => 4, 'deck' => '6-10', 'price' => 1833, 'code' => '3B'],
['category' => 'BALCONY', 'subcategory' => 'SPACIOUS BALCONY', 'type' => 'Quad', 'capacity' => 4, 'deck' => '9, 10', 'price' => 2033, 'code' => '1B'],
    //CHECKED
// SUITE
// SUITE - PANORAMIC SUITE (NO BALCONY)
['category' => 'SUITE', 'subcategory' => 'PANORAMIC SUITE (NO BALCONY)', 'type' => 'Quad', 'capacity' => 4, 'deck' => '12', 'price' => 2699, 'code' => 'VP'],
['category' => 'SUITE', 'subcategory' => 'PANORAMIC SUITE (NO BALCONY)', 'type' => 'Quint', 'capacity' => 5, 'deck' => '12', 'price' => 2299, 'code' => 'VP'],
['category' => 'SUITE', 'subcategory' => 'PANORAMIC SUITE (NO BALCONY)', 'type' => 'Sextuple', 'capacity' => 6, 'deck' => '12', 'price' => 1899, 'code' => 'VP'],

// SUITE - BALCONY SUITE
['category' => 'SUITE', 'subcategory' => 'BALCONY SUITE', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '9, 10', 'price' => 3133, 'code' => 'J4'],
['category' => 'SUITE', 'subcategory' => 'BALCONY SUITE', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '7, 9, 10, 11', 'price' => 3333, 'code' => 'J3'],
['category' => 'SUITE', 'subcategory' => 'BALCONY SUITE', 'type' => 'Triple', 'capacity' => 3, 'deck' => '7, 9, 10, 11', 'price' => 2599, 'code' => 'J3'],
['category' => 'SUITE', 'subcategory' => 'BALCONY SUITE', 'type' => 'Quad', 'capacity' => 4, 'deck' => '7, 9, 10, 11', 'price' => 1933, 'code' => 'J3'],

// SUITE - GRAND SUITE - 1 BEDROOM
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 1 BEDROOM', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '10', 'price' => 3999, 'code' => 'GS'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 1 BEDROOM', 'type' => 'Triple', 'capacity' => 3, 'deck' => '10', 'price' => 3199, 'code' => 'GS'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 1 BEDROOM', 'type' => 'Quad', 'capacity' => 4, 'deck' => '10', 'price' => 2399, 'code' => 'GS'],

// SUITE - GRAND SUITE - 2 BEDROOM
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 2 BEDROOM', 'type' => 'Quad', 'capacity' => 4, 'deck' => '8, 9', 'price' => 3133, 'code' => 'GT'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 2 BEDROOM', 'type' => 'Quint', 'capacity' => 5, 'deck' => '8, 9', 'price' => 2866, 'code' => 'GT'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 2 BEDROOM', 'type' => 'Sextuple', 'capacity' => 6, 'deck' => '8, 9', 'price' => 2599, 'code' => 'GT'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 2 BEDROOM', 'type' => 'Septuple', 'capacity' => 7, 'deck' => '8, 9', 'price' => 2333, 'code' => 'GT'],
['category' => 'SUITE', 'subcategory' => 'GRAND SUITE - 2 BEDROOM', 'type' => 'Octuple', 'capacity' => 8, 'deck' => '8, 9', 'price' => 2066, 'code' => 'GT'],

// SUITE - OWNER'S SUITE
['category' => 'SUITE', 'subcategory' => 'OWNER\'S SUITE', 'type' => 'Double Twin', 'capacity' => 2, 'deck' => '10', 'price' => 6666, 'code' => 'OS'],
['category' => 'SUITE', 'subcategory' => 'OWNER\'S SUITE', 'type' => 'Triple', 'capacity' => 3, 'deck' => '10', 'price' => 5333, 'code' => 'OS'],
['category' => 'SUITE', 'subcategory' => 'OWNER\'S SUITE', 'type' => 'Quad', 'capacity' => 4, 'deck' => '10', 'price' => 3999, 'code' => 'OS'],
    //CHECKED
];

foreach ($cabins as $cabinData) {
    $cabin = Cabin::create($cabinData);
    $capacity = $cabinData['capacity'];
    
    for ($i = 0; $i < $capacity; $i++) {
    Bed::create(['cabin_id' => $cabin->id]);
                }
            }
        }
    }
DB::table('cabins')->insert($cabins);
    }
}
