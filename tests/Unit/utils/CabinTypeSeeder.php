<?php

namespace Tests\Unit\utils;

use App\Models\CabinType;
use Illuminate\Database\Seeder;

class CabinTypeSeeder extends Seeder
{
    public function run(): void
    {
        CabinType::updateOrCreate(['id' => 1], ['cabin_type' => 'Private Cabin']);
        CabinType::updateOrCreate(['id' => 2], ['cabin_type' => 'Single Male']);
        CabinType::updateOrCreate(['id' => 3], ['cabin_type' => 'Single Female']);
    }
}
