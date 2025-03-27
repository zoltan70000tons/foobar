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
    Schema::create('adjustments', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique(); // Unique adjustment code
      $table->enum('type', ['DISCOUNT', 'ADDON']); // Type of adjustment
      $table->enum('operation', ['FIXED', 'PERCENTAGE'])->default('FIXED'); // Operation type
      $table->decimal('value', 10, 2); // Adjustment value
      $table->json('restrictions')->nullable(); // Restrictions as JSON
      $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // References events table
      $table->timestamps(); // Created and updated timestamps
      $table->boolean('system')->default(false);
      $table->unique(['event_id', 'code']); // Unique constraint for event_id and code
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('adjustment');
  }
};
