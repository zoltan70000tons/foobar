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
        Schema::table('users', function (Blueprint $table) {
            
            $table->string('zipcode')->nullable()->after('name');
            $table->string('state')->nullable()->after('name');
            $table->string('city')->nullable()->after('name');
            $table->string('lastname')->nullable()->after('name');
            $table->string('middlename')->nullable()->after('name');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('middlename');
            $table->dropColumn('lastname');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zipcode');
        });
    }
};
