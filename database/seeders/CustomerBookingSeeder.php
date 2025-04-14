<?php

namespace Database\Seeders;

use App\Helpers\CustomerHelper;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Event;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\SurvivorNumber;
use App\Models\User;
use App\Models\UserDetail;
use DateTime;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;
use Carbon\Carbon;

class CustomerBookingSeeder extends Seeder
{
  const GERMAN_SPEAKING_COUNTRIES = ['DEU', 'AUT', 'CHE', 'LIE', 'LUX'];
  const SPANISH_SPEAKING_COUNTRIES = ['ESP', 'MEX', 'ARG', 'COL', 'CHL', 'PER', 'VEN', 'ECU', 'GTM', 'CUB', 'BOL', 'DOM', 'HND', 'PRY', 'SLV', 'NIC', 'CRI', 'PAN', 'URY', 'PRT'];
  private int $counter = 1;

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::connection()->disableQueryLog(); // Disable query logging for memory efficiency

    $csvFilePath = database_path('seeders/data/customers.csv');
    $csv = Reader::createFromPath($csvFilePath, 'r');
    $csv->setHeaderOffset(0);

    $cachedEventIds = [];
    $cachedMembershipTypes = [];

    $batchSize = 200;
    $batch = [];

    foreach ($csv->getRecords() as $record) {
      $batch[] = $record;

      if (count($batch) >= $batchSize) {
        $this->processBatch($batch, $cachedEventIds, $cachedMembershipTypes);
        $batch = [];
        gc_collect_cycles();
      }
    }

    if (!empty($batch)) {
      $this->processBatch($batch, $cachedEventIds, $cachedMembershipTypes);
    }
  }

  private function processBatch(array $batch, array &$cachedEventIds, array &$cachedMembershipTypes)
  {
    foreach ($batch as $record) {
      try {
        DB::beginTransaction();

        $userId = Str::uuid()->toString();

        DB::table('users')->insert([
          'id' => $userId,
          'email' => null,
          'username' => null,
          'password' => Hash::make(Str::random(16)),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
          'organization_id' => 1,
        ]);

        $firstName = $record['Pax First Name'];
        $lastName = $record['Pax Last Name'];
        $middleName = $record['Pax Middle Name'] ?? null;
        $citizenship = $record['Pax Citizenship'];

        $language = 'en';

        if (in_array($citizenship, self::GERMAN_SPEAKING_COUNTRIES)) {
          $language = 'de';
        } elseif (in_array($citizenship, self::SPANISH_SPEAKING_COUNTRIES)) {
          $language = 'es';
        }

        DB::table('user_details')->insert([
          'user_id' => $userId,
          'first_name' => $firstName,
          'last_name' => $lastName,
          'middle_name' => $middleName,
          'gender' => $record['Pax Gender'],
          'citizenship' => $citizenship,
          'dob' => DateTime::createFromFormat('M d, Y', $record['Pax DOB'])?->format('Y-m-d'),
          'language' => $language,
          'phone' => str_replace(' ', '', $record['Pax Phone']) ?? null,
          'emergency_c_name' => $record['Pax Emergency Contact'] ?? null,
          'emergency_c_phone' => str_replace(' ', '', $record['Pax Phone']) ?? null,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

        DB::table('customer_addresses')->insert([
          'user_id' => $userId,
          'address_first' => $record['Pax Address Line'],
          'address_second' => $record['Pax Address Line2'] ?? null,
          'city' => $record['Pax City'],
          'state' => $record['Pax State'] ?? null,
          'postal_code' => $record['Pax Postal Code'],
          'country' => $record['Pax Country'],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

        $survivorNumber = $record['Survivor Number'] ?? CustomerHelper::generateSurvivorNumber();

        SurvivorNumber::create([
          'user_id' => $userId,
          'survivor_number' => $survivorNumber,
        ]);

        setPermissionsTeamId(1);
        DB::table('model_has_roles')->insert([
          'role_id' => 7,
          'model_type' => 'App\Models\User',
          'model_id' => $userId,
          'team_id' => 1,
        ]);

        // Process Attendance
        $attendance = [];
        foreach ($record as $key => $value) {
          $normalizedKey = str_replace('O', '0', $key);
          if (preg_match('/^(70K\d{4}|BTH\d{4})$/', $normalizedKey)) {
            $attendance[$normalizedKey] = $value == "1";
          }
        }

        $attendanceCount = count(array_filter($attendance, fn($value) => $value === true));

        if (!isset($cachedMembershipTypes[$attendanceCount])) {
          $membershipType = MembershipType::orderBy('booking_number_requirement', 'desc')
            ->where('booking_number_requirement', '<=', $attendanceCount)
            ->first();
          
          $cachedMembershipTypes[$attendanceCount] = $membershipType;
        } else {
          $membershipType = $cachedMembershipTypes[$attendanceCount];
        }

        if ($membershipType) {
          Membership::create([
            'user_id' => $userId,
            'membership_id' => $membershipType->id,
          ]);
        }

        // Process Event Bookings
        foreach ($attendance as $code => $attended) {
          if ($attended) {
            if (!isset($cachedEventIds[$code])) {
              $event = DB::table('events')->select('id')->where('code', $code)->first();
              $cachedEventIds[$code] = $event ? $event->id : null;
            }

            if ($cachedEventIds[$code]) {
              $booking = Booking::create([
                'event_id' => $cachedEventIds[$code],
                'booking_code' => hash('sha256', Str::uuid()->toString()),
                'customer_id' => $userId,
                'cabin_id' => null,
                'payment_plan' => 'PAY_IN_FULL',
                'is_single_occupancy' => false,
                'tags' => json_encode(["legacy"]),
                'agent_id' => null,
                'status' => 'UPLOADED',
              ]);

              $bookingId = $booking->id;
              // Add Passenger to booking
              Passenger::create(
                [
                  'booking_id' => $bookingId,
                  'survivor_number' => $survivorNumber,
                  'gender' => $record['Pax Gender'],
                  'first_name' => $firstName,
                  'last_name' => $lastName,
                  'middle_name' => $middleName,
                  'dob' => DateTime::createFromFormat('M d, Y', $record['Pax DOB'])?->format('Y-m-d'),
                  'citizenship' => $citizenship,
                  'phone' => str_replace(' ', '', $record['Pax Phone']) ?? null,
                  'email' => $record['Pax eMail'] ?? null,
                  'emergency_c_name' => $record['Pax Emergency Contact'] ?? null,
                  'emergency_c_phone' => str_replace(' ', '', $record['Pax Phone']) ?? null,
                  'address_first' => $record['Pax Address Line'],
                  'address_second' => $record['Pax Address Line2'] ?? null,
                  'city' => $record['Pax City'],
                  'state' => $record['Pax State'] ?? null,
                  'postal_code' => $record['Pax Postal Code'],
                  'country' => $record['Pax Country'],
                  'passenger_allocated_cost' => 0,
                  'passenger_balance' => 0,
                  'created_at' => Carbon::now(),
                  'updated_at' => Carbon::now(),
                ]
              );
              echo "Created booking for user: {$userId}\n";
            }
          }
        }

        DB::commit();
      } catch (Exception $e) {
        DB::rollBack();
        \Log::error("Error processing record: {$this->counter} - {$e->getMessage()}");
        throw $e; // Stop the process entirely if an error occurs
      }

      $this->counter++;

      if ($this->counter % 500 === 0) {
        DB::disconnect();
        gc_collect_cycles();
      }
    }
  }
}
