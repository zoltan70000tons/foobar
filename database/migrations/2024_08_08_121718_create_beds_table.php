<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBedsTable extends Migration
{
    public function up()
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('beds');
    }
};
