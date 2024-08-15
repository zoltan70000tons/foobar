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
    Schema::create('discounts', function (Blueprint $table) {
      $table->id();
      $table->string('discount_code', 50)->unique();
      $table->json('restrictions')->nullable();
      $table->string('discount_type', 50);
      $table->decimal('discount_value', 10, 2);
      $table->foreignId('event_id')->nullable()->constrained('events');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('discounts');
  }
};
