<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('customers', function (Blueprint $table) {
      //$table->id()->from(1000);
      $table->uuid('id')->primary()->unique()->index();
      $table->string('name')->unique();
      $table->string('survivor_number')->unique();
      $table->string('email')->nullable();
      $table->string('password');
      $table->boolean('policy')->default(false);
      $table->timestamp('email_verified_at')->nullable();
      $table->rememberToken();
      $table->timestamps();
    });

    // if (DB::connection()->getDriverName() === 'sqlite') {
    //   DB::table('customers')->insert(['id' => 999, 'name' => 'dummy', 'survivor_number' => 'dummy_number', 'password' => 'dummy_password']);
    //   DB::table('customers')->where('id', 999)->delete();
    // }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('customers');
  }
};
