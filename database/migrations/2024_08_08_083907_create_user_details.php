<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->foreignId('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('gender', 50)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->date('dob')->nullable();
            $table->string('citizenship', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('avatar')->nullable();
            $table->string('emergency_c_name', 255)->nullable();
            $table->string('emergency_c_phone', 20)->nullable();
            $table->string('language', 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('user_details');
    }
};
