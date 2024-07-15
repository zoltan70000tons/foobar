<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('system')->nullable()->default(0);
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('system')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('system')->nullable()->default(0);
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('system')->nullable()->default(0);
        });
    }
};
