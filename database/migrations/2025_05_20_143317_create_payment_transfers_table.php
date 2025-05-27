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
        Schema::create('payment_transfers', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->foreignId('payment_id_from') // ID of the payer (negative amount)
            ->constrained('payments') // Foreign key reference to the payments table
            ->onDelete('cascade'); // Delete the transfer if the payment is deleted
            $table->foreignId('payment_id_to') // ID of the receiver (positive amount)
            ->constrained('payments') // Foreign key reference to the payments table
            ->onDelete('cascade'); // Delete the transfer if the payment is deleted
            $table->foreignId('passenger_id_from')
            ->constrained('passengers')
            ->onDelete('cascade');
            $table->foreignId('passenger_id_to')
                ->constrained('passengers')
                ->onDelete('cascade');
            $table->timestamps(); // Created at and Updated at columns
            $table->softDeletes(); // For safe delete functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transfers');
    }
};
