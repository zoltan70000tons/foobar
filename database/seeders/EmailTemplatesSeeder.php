<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                DB::table('email_templates')->insert([
                    'event_id' => 1,
                    'name' => $filename,
                    'subject' => $subject,
                    'body' => $body,
                    'placeholders' => null,
                    'lang' => $lang,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // $this->command->info("Inserted: $filename ($lang)");
                $this->command->info("Inserted: $subject");
            }
        }
    }

    function removeXXWords($text)
    {
        return trim(preg_replace('/XX[^ ]+XX|\bX+\b/', '', $text));
    }
}
