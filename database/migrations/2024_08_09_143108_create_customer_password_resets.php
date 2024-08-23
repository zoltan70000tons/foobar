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
    Schema::create('customer_password_resets', function (Blueprint $table) {
      $table->id();
      $table->uuid('customer_id')->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
      $table->string('token')->unique()->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('customer_password_resets');
  }
};
