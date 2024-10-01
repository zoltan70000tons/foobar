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
      $table->uuid('customer_id')->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade')->nullable();
      $table->string('payment_plan', 50);
      $table->boolean('carbon_offset')->default(false);
      $table->foreignId('cabin_id')->constrained('cabins');
      $table->boolean('self_assigned')->default(false); // False by default, indicating cabin was assigned by app
      $table->boolean('completed')->default(false); // False by default, indicating booking is in progress
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
