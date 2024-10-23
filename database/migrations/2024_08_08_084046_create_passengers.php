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
    Schema::create('passengers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('booking_id')->constrained('bookings');
      $table->boolean('confirmed_booking_email')->default(false);
      $table->boolean('lead_passenger')->default(false);
      $table->integer('survivor_number')->nullable();
      $table->string('payment_method', 255);
      $table->string('gender', 50);
      $table->string('first_name', 255);
      $table->string('middle_name', 255)->nullable();
      $table->string('last_name', 255);
      $table->date('dob');
      $table->string('citizenship', 255);
      $table->string('address_first', 255);
      $table->string('address_second', 255)->nullable();
      $table->string('city', 255);
      $table->string('state', 255)->nullable();
      $table->string('postal_code', 50);
      $table->string('country', 50);
      $table->string('email', 255);
      $table->string('phone', length: 20);
      $table->string('emergency_c_name', 255);
      $table->string('emergency_c_phone', length: 20);
      $table->text('special_request')->nullable();
      $table->string('hear_about', 255);
      $table->boolean('newsletter')->default(false);
      $table->boolean('travel_info')->default(false);
      $table->boolean('terms_n_cons')->default(false);
      $table->boolean('cabin_conf_accp')->default(false);
      $table->boolean('single_t_agreement')->default(false);
      $table->decimal('passenger_allocated_cost', 10, 2);
      $table->decimal('passenger_balance', 10, 2);
      $table->boolean('was_on_board')->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('passengers');
  }
};
