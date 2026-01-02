<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    //php artisan logs:create-year-partitions 2026 - example to generate the 2026 year
    public function up(): void {
        DB::statement("
            CREATE TABLE logs (
                id UUID NOT NULL,
                action VARCHAR(80) NOT NULL,
                actor_type VARCHAR(10),
                actor_id UUID,
                related_type VARCHAR(20) NOT NULL,
                related_id VARCHAR(64) NOT NULL,
                description TEXT NOT NULL,
                payload JSONB,
                created_at TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
                CONSTRAINT logs_actor_type_chk CHECK (actor_type IN ('agent','system','customer')),
                CONSTRAINT logs_related_type_chk CHECK (related_type IN ('booking','customer','cabin','user','event'))
            ) PARTITION BY LIST (related_type);
        ");
    }

    public function down(): void {
        DB::statement('DROP TABLE IF EXISTS logs CASCADE');
    }
};
