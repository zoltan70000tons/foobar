<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use File;

class EmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $htmlPathEn = base_path('database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'html_templates' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'booking-confirmation.html');
        $htmlContentEn = File::get($htmlPathEn);
        $htmlPathEs = base_path('database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'html_templates' . DIRECTORY_SEPARATOR . 'es' . DIRECTORY_SEPARATOR . 'booking-confirmation.html');
        $htmlContentEs = File::get($htmlPathEs);
        $htmlPathDe = base_path('database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'html_templates' . DIRECTORY_SEPARATOR . 'de' . DIRECTORY_SEPARATOR . 'booking-confirmation.html');
        $htmlContentDe = File::get($htmlPathDe);
        DB::table('email_templates')->insert([
            [
                'name' => 'Booking Confirmation',
                'subject' => 'Your 70000TONS OF METAL Booking Confirmation XX-$6000XX',
                'lang' => 'en',
                'body' => $htmlContentEn,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'event_id' => 1,
            ],
            [
                'name' => 'Booking Confirmation',
                'subject' => 'XXBC-$6000XX Tu Confirmación de Reserva de 70000TONS OF METAL',
                'lang' => 'es',
                'body' => $htmlContentEs,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'event_id' => 1,
            ],
            [
                'name' => 'Booking Confirmation',
                'subject' => 'XXBC-$6000XX Deine 70000TONS OF METAL Buchungsbestätigung',
                'lang' => 'de',
                'body' => $htmlContentDe,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'event_id' => 1,
            ],
           
        ]);
    }
}
