<?php

namespace App\Console\Commands;

use App\Services\CabinInventoryIntegrityService;
use Illuminate\Console\Command;

class CabinInventoryIntegrityCheck extends Command {
    protected $signature = 'cabins:inventory-integrity';
    protected $description = 'Validate cabin inventory integrity and email a daily report.';

    public function handle(CabinInventoryIntegrityService $service): int {
        $report = $service->run();
        $emailSent = $service->sendReport($report);

        $issues = $report['summary']['issues_count'] ?? 0;
        $this->info("Cabin inventory integrity check completed. Issues found: {$issues}.");

        if (!$emailSent) {
            $this->warn('Integrity report email was not sent (no recipients or mail failure).');
        }

        return self::SUCCESS;
    }
}
