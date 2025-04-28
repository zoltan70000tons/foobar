<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EmailTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('email_templates')->truncate();
        $languages = ['de', 'en', 'es'];
        $basePath = base_path('database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . 'html_templates');

        // Subjects that should have priority 1
        $prioritySubjects = [
            'Your New 70000TONS OF METAL Booking Confirmation',
            'Your Invoice and 70000TONS OF METAL Booking Confirmation',
            'Your 70000TONS OF METAL Booking Confirmation',
            'Tu Confirmación de Reserva de 70000TONS OF METAL',
            'Tu factura y Confirmación de Reserva de 70000TONS OF METAL',
            'Tu nueva Confirmación de Reserva de 70000TONS OF METAL',
            'Deine 70000TONS OF METAL Buchungsbestätigung',
            'Deine 70000TONS OF METAL Rechnung und Buchungsbestätigung',
            'Deine neue 70000TONS OF METAL Buchungsbestätigung',
        ];

        // Names that should be hidden
        $hiddenNames = [
            '70000TONS_email_footer_ENG',
            '70000TONS_email_header_ENG',
            '70000TONS_email_header_DEU',
            '70000TONS_email_footer_DEU',
            '70000TONS_email_header_ESP',
            '70000TONS_email_footer_ESP',
        ];

        foreach ($languages as $lang) {
            $dirPath = $basePath . DIRECTORY_SEPARATOR . $lang;

            if (!File::exists($dirPath) || !File::isDirectory($dirPath)) {
                $this->command->warn("Directory not found: $dirPath");
                continue;
            }
            $files = File::files($dirPath);

            foreach ($files as $file) {
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $filename = str_replace('.blade', '', pathinfo($filename, PATHINFO_FILENAME));
                $subject = $this->removeXXWords($filename);
                $body = File::get($file->getPathname());

                // Determine priority
                $priority = Str::startsWith($subject, $prioritySubjects) ? 1 : 10;

                // Determine if hidden
                $hidden = in_array($filename, $hiddenNames);

                DB::table('email_templates')->insert([
                    'event_id' => 1,
                    'name' => $filename,
                    'subject' => $subject,
                    'body' => $body,
                    'placeholders' => null,
                    'lang' => $lang,
                    'priority' => $priority,
                    'hidden' => $hidden,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("Inserted: $subject");
            }
        }
    }

    function removeXXWords($text)
    {
        return trim(preg_replace('/XX[^ ]+XX|\bX+\b/', '', $text));
    }
}
