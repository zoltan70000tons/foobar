<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('cabin_specs', function (Blueprint $table) {
            $table->id();
            $table->string('cabin_number', 50)->unique();
            $table->integer('deck');
            $table->integer('total_berths');
            $table->string('lower_bed_type_1', 5)->nullable();
            $table->string('lower_bed_type_2', 5)->nullable();
            $table->string('upper_berths', 5)->nullable();
            $table->boolean('accessible')->default(false);
            $table->integer('connects_with')->nullable(); // Cabin ID of the connected cabin
            $table->string('location', 2);
            $table->boolean('balcony')->default(false);
            $table->boolean('obstructed_view')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cabin_specs');
    }
};
