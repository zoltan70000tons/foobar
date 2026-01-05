<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingAgentSessionsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('booking_agent_sessions', function (Blueprint $table) {
            // Create the 'id' column as an auto-incrementing primary key
            $table->id();

            // Create 'booking_id' column as an unsigned BIGINT (foreign key reference to bookings table)
            $table->unsignedBigInteger('booking_id');

            // Create 'agent_id' column as an unsigned BIGINT (foreign key reference to users table)
            $table->uuid('agent_id');

            // Create 'time' column as a DATETIME type with the current timestamp as default
            $table->timestamp('time')->useCurrent();

            // Define foreign key constraint for 'booking_id' referencing 'id' in the bookings table
            // The 'onDelete' cascade ensures that related sessions are deleted if the associated booking is deleted
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');

            // Define foreign key constraint for 'agent_id' referencing 'id' in the users table
            // The 'onDelete' cascade ensures that related sessions are deleted if the associated agent is deleted
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');

            // Optionally, create an index on 'booking_id' and 'agent_id' for faster lookups
            $table->index(['booking_id', 'agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // Drop the table if it exists
        Schema::dropIfExists('booking_agent_sessions');
    }
}
