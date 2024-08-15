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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('address');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('one_day_event');
            $table->date('sales_period_start_date')->nullable();
            $table->date('sales_period_end_date')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->foreignId('organization_id')->constrained('organizations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
