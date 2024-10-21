<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up()
  {
    Schema::create('bookings', function (Blueprint $table) {
      $table->id();
      $table->string('booking_code')->unique(); // Unique booking code e.g. Cabin Number + Random String
      $table->uuid('customer_id')->references('id')->on('users')->onDelete('cascade'); // References users table
      $table->enum('payment_plan', ['PAY_IN_FULL', '4_INSTALLMENTS', '3_INSTALLMENTS'])->default('PAY_IN_FULL'); // Enum for payment plan
      $table->foreignId('cabin_id')->constrained('cabins'); // References cabins table
      $table->boolean('completed')->default(false); // Indicates if booking is completed
      $table->boolean('is_cancelled')->default(false); // Indicates if booking is cancelled
      $table->boolean('is_single_occupancy')->default(false); // Indicates if booking is for single occupancy
      $table->jsonb('tags')->default(json_encode(['not-assigned'])); // JSONB field for tags
      $table->timestamps(); // created_at and updated_at timestamps
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
