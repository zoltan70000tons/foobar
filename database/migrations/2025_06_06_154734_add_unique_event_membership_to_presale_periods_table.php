<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('presale_periods', function (Blueprint $table) {
            $table->unique(['event_id', 'membership_type_id'], 'presale_periods_event_membership_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presale_periods', function (Blueprint $table) {
            $table->dropUnique('presale_periods_event_membership_unique');
        });
    }
};
