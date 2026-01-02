<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('cabins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_type_id')->constrained('cabin_types');
            $table->foreignId('cabin_category_id')->constrained('cabin_categories');
            $table->foreignId('cabin_spec_id')->constrained('cabin_specs'); // Link to cabin_specs table
            $table->integer('inventory')->default(1);
            $table->string('notes', 255)->nullable();
            $table
                ->enum('status', ['AVAILABLE', 'RESERVED', 'BOOKED', 'PARTIALLY_BOOKED', 'CLOSED'])
                ->default('RESERVED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cabins');
    }
};
