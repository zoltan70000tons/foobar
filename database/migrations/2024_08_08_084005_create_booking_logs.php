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
    Schema::create('booking_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('booking_id')->constrained('bookings');
      $table->uuid('user_id')->foreignId('user_id')->references('id')->on('users');
      $table->string('action', 255);
      $table->string('description', 255)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('booking_logs');
  }
};
