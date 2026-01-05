<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('passengers', function (Blueprint $table) {
            $table->unsignedTinyInteger('survivor_sync_attempts')->default(0)->after('survivor_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn('survivor_sync_attempts');
        });
    }
};
