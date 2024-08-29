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
    Schema::create('bookings', function (Blueprint $table) {
      $table->id();
      $table->string('booking_code', 255);
      $table->uuid('customer_id')->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
      $table->string('payment_method', 50);
      $table->boolean('carbon_offset')->default(false);
      $table->foreignId('cabin_id')->nullable()->constrained('cabins');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('bookings');
  }
};
