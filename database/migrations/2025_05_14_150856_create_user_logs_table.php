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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id')->comment('The customer this comment belongs to.');
            $table->uuid('author_id')->comment('The person or system that triggered the action. Can be an agent, customer, or system.');
            $table->string('action', 255)->comment('Type of action performed (e.g., PASSWORD_RESET, PROFILE_UPDATE).');
            $table->text('description')->comment('Optional detail about what was changed or triggered.');
            $table->timestamps();
            $table->softDeletes(); // Adds the `deleted_at` column
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
