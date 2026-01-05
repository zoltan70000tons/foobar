<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_action_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('action_code');
            $table->unsignedBigInteger('event_id');

            $table->dateTime('applies_from_date')->nullable();
            $table->dateTime('applies_until_date')->nullable();

            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->boolean('is_blocking')->default(false);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['event_id', 'action_code']);
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('booking_action_rules');
    }
};
