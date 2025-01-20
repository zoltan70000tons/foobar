<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create("passengers", function (Blueprint $table) {
      $table->id();
      $table->foreignId("booking_id")->constrained("bookings")->onDelete("cascade");
      $table->boolean("confirmed_booking_email")->default(false);
      $table->boolean("lead_passenger")->default(false);
      $table->string("survivor_number", 9)->nullable();
      $table->enum("payment_method", ["CREDIT_CARD", "BANK_TRANSFER"])->default("CREDIT_CARD");
      $table->string("gender", 50)->nullable();
      $table->string("first_name", 255)->nullable();
      $table->string("middle_name", 255)->nullable();
      $table->string("last_name", 255)->nullable();
      $table->date("dob")->nullable();
      $table->string("citizenship", 255)->nullable();
      $table->string("address_first", 255)->nullable();
      $table->string("address_second", 255)->nullable();
      $table->string("city", 255)->nullable();
      $table->string("state", 255)->nullable();
      $table->string("postal_code", 50)->nullable();
      $table->string("country", 50)->nullable();
      $table->string("email", 255)->nullable();
      $table->string("phone", length: 20)->nullable();
      $table->string("emergency_c_name", 255)->nullable();
      $table->string("emergency_c_phone", length: 20)->nullable();
      $table->text("special_request")->nullable()->nullable();
      $table->jsonb("special_options")->nullable();
      $table->string("hear_about", 255)->nullable();
      $table->string("referral_details")->nullable();
      $table->boolean("newsletter")->default(false);
      $table->boolean("travel_info")->default(false);
      $table->boolean("terms_n_cons")->default(false);
      $table->boolean("empty_seat")->default(false);
      $table->boolean("cabin_conf_accp")->default(false);
      $table->boolean("single_t_agreement")->default(false);
      $table->decimal("passenger_allocated_cost", 10, 2);
      $table->decimal("passenger_balance", 10, 2);
      $table->boolean("was_on_board")->default(false);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists("passengers");
  }
};
