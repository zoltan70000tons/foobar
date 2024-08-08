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
    Schema::create('booking_has_discounts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('discount_id')->constrained('discounts');
      $table->foreignId('booking_id')->constrained('bookings');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('booking_has_discounts');
  }
};
