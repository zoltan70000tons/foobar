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
    Schema::create('customer_details', function (Blueprint $table) {
      $table->id();
      $table->uuid('user_id');
      $table->string('survivor_number')->unique()->nullable();
      $table->string('gender', 2);
      $table->string('first_name', 100);
      $table->string('middle_name', 100)->nullable();
      $table->string('last_name', 100);
      $table->date('dob');
      $table->string('citizenship', 3);
      $table->string('phone', 20);
      $table->text('avatar')->nullable();
      $table->string('emergency_c_name', 255);
      $table->string('emergency_c_phone', 20);
      $table->string('language', 4);
      $table->timestamps();

      // Define foreign key constraint
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('customer_details');
  }
};
