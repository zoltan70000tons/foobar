<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('booking_has_adjustments', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade'); // References bookings table
            $table->foreignId('adjustment_id')->constrained('adjustments')->onDelete('cascade'); // References the adjustments table
            $table->primary(['booking_id', 'adjustment_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('booking_has_adjustments');
    }
};
