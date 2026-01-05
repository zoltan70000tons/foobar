<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        //extend when needed
        $types = ['booking', 'customer', 'cabin', 'user', 'event'];
        foreach ($types as $type) {
            // Create type-level partition
            DB::statement("
                CREATE TABLE logs_{$type}
                PARTITION OF logs
                FOR VALUES IN ('{$type}')
                PARTITION BY RANGE (created_at);
            ");

            // Create initial year partition for 2025
            DB::statement("
                CREATE TABLE logs_{$type}_2025
                PARTITION OF logs_{$type}
                FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
            ");

            // Add indexes (customize as needed)
            DB::statement("CREATE INDEX logs_{$type}_2025_created_at_idx ON logs_{$type}_2025 (created_at)");
            DB::statement("CREATE INDEX logs_{$type}_2025_related_id_idx ON logs_{$type}_2025 (related_id)");
        }
    }

    public function down(): void {
        //extend when needed
        $types = ['booking', 'customer', 'cabin', 'user', 'event'];
        foreach ($types as $type) {
            DB::statement("DROP TABLE IF EXISTS logs_{$type}_2025 CASCADE");
            DB::statement("DROP TABLE IF EXISTS logs_{$type} CASCADE");
        }
    }
};
