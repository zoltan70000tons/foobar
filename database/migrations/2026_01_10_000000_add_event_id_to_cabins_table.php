<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Add event_id column to cabins table for performance optimization.
     * This denormalization allows direct event filtering without JOINs,
     * significantly improving the "shared basket" cabin update logic.
     */
    public function up(): void {
        Schema::table('cabins', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('cabin_category_id');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });

        // Backfill existing data from cabin_categories
        DB::statement('
            UPDATE cabins 
            SET event_id = (
                SELECT event_id 
                FROM cabin_categories 
                WHERE cabin_categories.id = cabins.cabin_category_id
            )
        ');

        // Make event_id non-nullable after backfill
        Schema::table('cabins', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
        });

        // Add composite index for optimized shared basket queries
        Schema::table('cabins', function (Blueprint $table) {
            $table->index(['cabin_spec_id', 'event_id'], 'idx_cabins_spec_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('cabins', function (Blueprint $table) {
            $table->dropIndex('idx_cabins_spec_event');
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }
};
