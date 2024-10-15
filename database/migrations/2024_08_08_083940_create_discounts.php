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
      $table->string('code')->unique(); // Unique discount code
      $table->json('restrictions')->nullable(); // Restrictions as JSON (optional)
      $table->enum('type', ['FIXED', 'PERCENTAGE'])->default('PERCENTAGE'); // Enum for operation_type
      $table->decimal('value', 10, 2); // Discount value
      $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // References events table
      $table->timestamps();
      
      // Unique index for event_id and code combination
      $table->unique(['event_id', 'code']);
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
