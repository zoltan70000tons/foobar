<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create("cabins", function (Blueprint $table) {
      $table->id();
      $table->foreignId("cabin_type_id")->constrained("cabin_types");
      $table->foreignId("cabin_category_id")->constrained("cabin_categories");
      $table->string("cabin_number", 50)->unique();
      $table->integer("deck");
      $table->integer("total_berths");
      $table->string("lower_bed_type_1", 5)->nullable();
      $table->string("lower_bed_type_2", 5)->nullable();
      $table->string("upper_berths", 5)->nullable();
      $table->boolean("accessible")->default(false);
      $table->integer("connects_with")->nullable();
      $table->string("location", 2);
      $table->boolean("balcony")->default(false);
      $table->boolean("obstructed_view")->default(false);
      $table->integer("inventory")->default(1);
      $table->string("notes", 255)->nullable();
      $table->jsonb("tags")->default(json_encode(["NOT ASSIGNED"]));
      $table->enum("status", ["AVAILABLE", "RESERVED", "BOOKED", "PARTIALLY_BOOKED", "CLOSED"])->default("RESERVED");
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists("cabins");
  }
};
