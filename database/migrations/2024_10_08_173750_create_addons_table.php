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
    Schema::create('addons', function (Blueprint $table) {
      $table->id();
      $table->string('code'); // Name of the addon (e.g., CARBON_OFFSET)
      $table->enum('type', ['FIXED', 'PERCENTAGE'])->default('FIXED'); // Enum for operation_type
      $table->decimal('value', 10, 2); // Discount value for the addon
      $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // References events table
      $table->timestamps(); // created_at and updated_at timestamps

      // Unique index for event_id and name combination
      $table->unique(['event_id', 'code']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('addons');
  }
};
