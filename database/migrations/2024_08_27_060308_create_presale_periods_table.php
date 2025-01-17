<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresalePeriodsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('presale_periods', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('cascade');
      $table->foreignId('membership_type_id')->constrained('membership_types')->onDelete('cascade');
      $table->dateTime('start_date');
      $table->dateTime('end_date');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('presale_periods');
  }
}
