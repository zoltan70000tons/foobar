<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('cabin_category_specs', function (Blueprint $table) {
            $table->id();
            $table->string('category_type', 255);
            $table->string('category_code', 5);
            $table->string('category_name', 255);
            $table->integer('capacity');
            $table->jsonb('description'); // JSONB column for multi-language descriptions
            $table->text('iframe')->nullable();
            $table->json('images')->nullable();
            $table->string('decks', 255);
            $table->integer('display_order');
            $table->foreignId('cruise_id')->constrained('cruises');
            $table->string('category_number', 5)->nullable();
            $table->unique(['category_code', 'capacity', 'cruise_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cabin_category_specs');
    }
};
