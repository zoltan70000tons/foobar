<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\MembershipType;
use Illuminate\Container\Container;
use Faker\Generator;

class PresalePeriodSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch the ID of the first event
        $eventId = Event::first()->id;

        // Fetch all membership types
        $membershipTypes = MembershipType::all();

        // Initial presale period start date
        $initialPresaleStartDate = Carbon::create(2025, 1, 1);
        
        // Presale end date (same for all)
        $presaleEndDate = Carbon::create(2025, 7, 12);

        // Create presale periods for each membership type
        foreach ($membershipTypes as $index => $membershipType) {
            $presaleStartDate = $initialPresaleStartDate->copy()->addDays(10 * $index);

            DB::table('presale_periods')->insert([
                'id' => $this->faker->uuid,
                'event_id' => $eventId,
                'membership_type_id' => $membershipType->id,
                'start_date' => $presaleStartDate,
                'end_date' => $presaleEndDate,
            ]);
        }
    }
}
