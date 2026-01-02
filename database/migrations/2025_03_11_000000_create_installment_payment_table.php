<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('installment_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('status', ['PAID', 'PENDING', 'OVERDUE', 'PARTIALLY_PAID'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('installment_payment');
    }
};
