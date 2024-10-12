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
    Schema::create('customer_addresses', function (Blueprint $table) {
      $table->id();
      $table->uuid('user_id');
      $table->string('address_first', 255);
      $table->string('address_second', 255)->nullable();
      $table->string('city', 255);
      $table->string('state', 255);
      $table->string('postal_code', 10);
      $table->string('country', 3);
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
    Schema::dropIfExists('customer_addresses');
  }
};
