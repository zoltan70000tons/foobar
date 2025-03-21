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
        Schema::create('passenger_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('passengers');
            $table->string('type', 50);
            $table->enum('operation', ['FIXED', 'PERCENTAGE'])->default('FIXED');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_discounts');
    }
};
