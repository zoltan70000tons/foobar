<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('potential_survivor_matches', function (Blueprint $table) {
            $table->id();

            // Passenger details
            $table->unsignedBigInteger('passenger_id');
            $table->string('passenger_first_name');
            $table->string('passenger_last_name');
            $table->date('passenger_dob');

            // User details match
            $table->unsignedBigInteger('user_detail_id');
            $table->string('user_first_name');
            $table->string('user_last_name');
            $table->date('user_dob');

            // Matching metadata
            $table->float('score'); // final score 0-100
            $table
                ->enum('status', ['in_progress', 'reviewed', 'approved', 'rejected', 'resolved'])
                ->default('in_progress');
            $table->enum('type', ['match', 'double_booking'])->default('match');
            $table->timestamp('review_date')->nullable();
            $table->uuid('reviewer_id')->nullable();

            $table->timestamps();

            $table->foreign('passenger_id')->references('id')->on('passengers')->cascadeOnDelete();
            $table->foreign('user_detail_id')->references('id')->on('user_details')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('potential_survivor_matches');
    }
};
