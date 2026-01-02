<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class CleanTemporaryFiles extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-temporary-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        $path = storage_path('app');
        $files = collect(File::allFiles($path))->reject(function ($file) {
            return str_contains($file->getPath(), 'public') || $file->getExtension() === 'csv';
        });

        foreach ($files as $file) {
            if (Carbon::createFromTimestamp($file->getMTime())->lessThan(now()->subDays(2))) {
                File::delete($file->getPathname());
            }
        }

        $this->info('Temporary files deleted.');
    }
}
