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
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('one_day_event');
            $table->dateTime('sales_period_start_date');
            $table->dateTime('sales_period_end_date');
            $table->string('status');
            $table->integer('organization_id');
            $table->timestamps();
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
