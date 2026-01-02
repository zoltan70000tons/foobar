<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('cabin_categories', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 10, 2);
            $table->foreignId('cabin_category_spec_id')->constrained('cabin_category_specs');
            $table->foreignId('event_id')->constrained('events');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cabin_categories');
    }
};
