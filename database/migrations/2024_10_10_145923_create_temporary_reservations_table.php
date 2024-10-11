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
        Schema::create('temporary_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_id')->constrained('cabins')->onDelete('cascade');
            $table->uuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cabin_number', 50);
            $table->integer('inventory')->default(1);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['cabin_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_reservations');
    }
};
