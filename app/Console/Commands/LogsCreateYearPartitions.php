<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LogsCreateYearPartitions extends Command {
    protected $signature = 'logs:create-year-partitions {year?}';
    protected $description = 'Create yearly sub-partitions for each logs related_type';

    public function handle() {
        $year = $this->argument('year') ?? Carbon::now()->year + 1;
        $types = ['booking', 'customer', 'cabin', 'user', 'event'];

        foreach ($types as $type) {
            $partitionTable = "logs_{$type}_{$year}";

            // Check if it already exists
            $exists =
                DB::select("
                SELECT to_regclass('public.{$partitionTable}') IS NOT NULL AS exists
            ")[0]->exists ?? false;

            if ($exists) {
                $this->info("✅ Partition {$partitionTable} already exists. Skipping.");
                continue;
            }

            $from = "{$year}-01-01";
            $to = $year + 1 . '-01-01';

            $sql = "
                CREATE TABLE IF NOT EXISTS {$partitionTable}
                PARTITION OF logs_{$type}
                FOR VALUES FROM ('$from') TO ('$to');

                CREATE UNIQUE INDEX IF NOT EXISTS {$partitionTable}_id_unique ON {$partitionTable} (id);
                CREATE INDEX IF NOT EXISTS {$partitionTable}_created_at_idx ON {$partitionTable} (created_at);
                CREATE INDEX IF NOT EXISTS {$partitionTable}_related_id_idx ON {$partitionTable} (related_id);
            ";

            DB::unprepared($sql);
            $this->info("✅ Created partition: {$partitionTable}");
        }

        $this->info("🎉 All partitions for {$year} created successfully.");
    }
}
