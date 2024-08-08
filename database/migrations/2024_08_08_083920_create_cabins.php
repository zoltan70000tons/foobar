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
    Schema::create('cabins', function (Blueprint $table) {
      $table->id();
      $table->foreignId('cabin_category_id')->constrained('cabin_categories');
      $table->string('cabin_code', 50);
      $table->string('cabin_type', 50);
      $table->string('cabin_number', 50);
      $table->integer('capacity');
      $table->integer('deck');
      $table->integer('total_berths');
      $table->string('lower_bed_type_1', 5);
      $table->string('lower_bed_type_2', 5);
      $table->string('upper_berths', 5);
      $table->integer('connects_with')->nullable();
      $table->string('location', 2);
      $table->boolean('balcony');
      $table->boolean('obstructed_view');
      $table->integer('inventory');
      $table->string('notes', 255);
      $table->jsonb('tags')->nullable();
      $table->string('status', 100);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cabins');
  }
};
