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
    Schema::create('memberships', function (Blueprint $table) {
      $table->id();
      $table->uuid('customer_id')->foreignId('customer_id')->references('id')->on('customers')->onDelete('cascade');
      $table->foreignId('membership_id')->constrained('membership_types');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('memberships');
  }
};
