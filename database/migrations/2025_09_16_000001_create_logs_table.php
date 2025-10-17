<?php
// database/migrations/2025_09_17_000000_create_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action', 80); // p.ej. INVENTORY_CHANGED, PAYMENT_ADDED
            $table->string('actor_type', 10); // 'agent' | 'system' 
            $table->uuid('actor_id')->nullable(); 
            $table->string('related_type', 20); // 'booking' | 'customer' | 'cabin' (CHECK)
            $table->string('related_id', 64);   // ints/uuids 
            $table->text('description');
            $table->json('payload')->nullable(); 
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['related_type','related_id','created_at'], 'logs_related_timeline_idx');
            $table->index(['action','created_at'], 'logs_action_time_idx');
            $table->index(['actor_type','actor_id'], 'logs_actor_idx');
            $table->index('created_at', 'logs_created_at_idx');
        });

        // CHECK constraints (Postgres)
        DB::statement("ALTER TABLE logs
            ADD CONSTRAINT logs_actor_type_chk
            CHECK (actor_type IN ('agent','system'))");

        DB::statement("ALTER TABLE logs
            ADD CONSTRAINT logs_related_type_chk
            CHECK (related_type IN ('booking','customer','cabin','user','event'))");

        // index for performance by related_type
        DB::statement("CREATE INDEX IF NOT EXISTS logs_booking_created_idx
            ON logs (created_at) WHERE related_type = 'booking'");
        DB::statement("CREATE INDEX IF NOT EXISTS logs_customer_created_idx
            ON logs (created_at) WHERE related_type = 'customer'");
        DB::statement("CREATE INDEX IF NOT EXISTS logs_cabin_created_idx
            ON logs (created_at) WHERE related_type = 'cabin'");
        DB::statement("CREATE INDEX IF NOT EXISTS logs_user_created_idx
            ON logs (created_at) WHERE related_type = 'user'");
        DB::statement("CREATE INDEX IF NOT EXISTS logs_event_created_idx
            ON logs (created_at) WHERE related_type = 'event'");*/
    }

    public function down(): void
    {
        /*DB::statement("DROP INDEX IF EXISTS logs_booking_created_idx");
        DB::statement("DROP INDEX IF EXISTS logs_customer_created_idx");
        DB::statement("DROP INDEX IF EXISTS logs_cabin_created_idx");
        DB::statement("DROP INDEX IF EXISTS logs_user_created_idx");
        DB::statement("DROP INDEX IF EXISTS logs_event_created_idx");
        DB::statement("ALTER TABLE logs DROP CONSTRAINT IF EXISTS logs_actor_type_chk");
        DB::statement("ALTER TABLE logs DROP CONSTRAINT IF EXISTS logs_related_type_chk");
        Schema::dropIfExists('logs');*/
    }
};
