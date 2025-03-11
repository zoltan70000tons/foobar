<?php

  namespace Database\Seeders;

  use App\Helpers\CustomerHelper;
  use App\Models\Booking;
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
  use League\Csv\Reader;

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
            'password' => bin2hex(random_bytes(16)),
          ]);

          $firstName = strtolower(preg_replace('/[^a-zA-Z]/', '', $record['First Name']));
          $lastNameParts = explode(' ', trim($record['Last Name']));
          $lastName = array_shift($lastNameParts);
          $middleName = implode(' ', $lastNameParts);
          $citizenship = $record['CTZ'];

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
            'gender' => $record['Gender'],
            'citizenship' => $record['CTZ'],
            'dob' => DateTime::createFromFormat('M d, Y', $record['DOB'])?->format('Y-m-d'),
            'language' => $language,
          ]);

          $survivorNumber = $record['SURVIVOR NUMBER'] ?? CustomerHelper::generateSurvivorNumber();

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
            $membershipType = MembershipType::orderBy('booking_number_requirement')
              ->where('booking_number_requirement', '<=', $attendanceCount)
              ->latest('booking_number_requirement')
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
                Booking::create([
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
                echo "Created booking for user: {$userId}\n";
              }
            }
          }

          DB::commit();
          echo "Customer number {$this->counter} with id: {$userId} was committed!\n";
        } catch (Exception $e) {
          DB::rollBack();
          echo "Error with customer number {$this->counter}: " . $e->getMessage() . "\n";
        }

        $this->counter++;

        if ($this->counter % 500 === 0) {
          DB::disconnect();
          gc_collect_cycles();
        }
      }
    }
  }
