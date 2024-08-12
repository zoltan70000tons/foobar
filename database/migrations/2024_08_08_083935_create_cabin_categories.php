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
    Schema::create('cabin_categories', function (Blueprint $table) {
      $table->id();
      $table->string('category_type', 255);
      $table->string('category_code', 5);
      $table->string('category_name', 255);
      $table->integer('capacity');
      $table->text('description');
      $table->decimal('price', 10, 2);
      $table->integer('display_order');
      $table->foreignId('cruise_id')->nullable()->constrained('cruises');
      $table->foreignId('event_id')->nullable()->constrained('events');
      $table->unique(['id', 'cruise_id']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('cabin_categories');
  }
};
