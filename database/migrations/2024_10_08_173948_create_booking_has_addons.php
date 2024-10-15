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
    Schema::create('booking_has_addons', function (Blueprint $table) {
      $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade'); // References bookings table
      $table->foreignId('addon_id')->constrained('addons')->onDelete('cascade'); // References discounts table
      $table->primary(['booking_id', 'addon_id']); // Composite primary key
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('booking_has_addons');
  }
};
