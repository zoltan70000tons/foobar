<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Create the 'user_details' table
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
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
        });

        // Create the 'addresses' table
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('address_first', 255)->nullable();
            $table->string('address_second', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('postal_code', 50)->nullable();
            $table->string('country', 50)->nullable();
        });

        // Create the 'cabins' table
        Schema::create('cabins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabin_category_id')->constrained('cabin_categories');
            $table->string('cabin_code', 50);
            $table->string('cabin_type', 50);
            $table->string('cabin_number', 50);
            $table->integer('capacity');
            $table->integer('deck');
            $table->integer('total_berths');
            $table->string('lower_bed_type_1', 5);
            $table->string('lower_bed_type_2', 5);
            $table->string('upper_berths', 5);
            $table->integer('connects_with')->nullable();
            $table->string('location', 2);
            $table->boolean('balcony');
            $table->boolean('obstructed_view');
            $table->integer('inventory');
            $table->string('notes', 255);
            $table->jsonb('tags')->nullable();
            $table->string('status', 100);
        });

        // Create the 'cruises' table
        Schema::create('cruises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
        });

        // Create the 'cabin_categories' table
        Schema::create('cabin_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_type', 255);
            $table->string('category_code', 5);
            $table->string('category_name', 255);
            $table->integer('capacity');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('display_order');
            $table->foreignId('cruise_id')->nullable()->constrained('cruises');
            $table->foreignId('event_id')->nullable()->constrained('events');
            $table->unique(['id', 'cruise_id']);
        });

        // Create the 'discounts' table
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('discount_code', 50)->unique();
            $table->json('restrictions')->nullable();
            $table->string('discount_type', 50);
            $table->decimal('discount_value', 10, 2);
            $table->foreignId('event_id')->nullable()->constrained('events');
        });

        // Create the 'booking_has_discounts' table
        Schema::create('booking_has_discounts', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained('discounts');
            $table->foreignId('booking_id')->constrained('bookings');
        });

        // Create the 'bookings' table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 255);
            $table->foreignId('user_id')->constrained('users');
            $table->string('payment_method', 50);
            $table->boolean('carbon_offset')->default(false);
            $table->foreignId('cabin_id')->nullable()->constrained('cabins');
            $table->timestamps();
        });

        // Create the 'booking_logs' table
        Schema::create('booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('agent_id')->constrained('users');
            $table->string('action', 255);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // Create the 'booking_comments' table
        Schema::create('booking_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('agent_id')->constrained('users');
            $table->text('comment');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // Create the 'cabin_types' table
        Schema::create('cabin_types', function (Blueprint $table) {
            $table->id();
            $table->string('cabin_type', 50);
            $table->string('cabin_type_description', 255)->nullable();
        });

        // Modify the 'password_reset_tokens' table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
        });

        // Create the 'memberships' table
        Schema::create('memberships', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('membership_id')->constrained('membership_type');
        });

        // Create the 'membership_type' table
        Schema::create('membership_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('stamp_image')->nullable();
            $table->integer('booking_number_requirement');
            $table->decimal('discount_value', 10, 2);
        });

        // Create the 'passengers' table
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->boolean('confirmed_booking_email')->default(false);
            $table->boolean('lead_passenger')->default(false);
            $table->integer('survivor_number')->nullable();
            $table->string('payment_method', 255);
            $table->string('gender', 50);
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255);
            $table->date('dob');
            $table->string('citizenship', 255);
            $table->string('address_first', 255);
            $table->string('address_second', 255)->nullable();
            $table->string('city', 255);
            $table->string('state', 255)->nullable();
            $table->string('postal_code', 50);
            $table->string('country', 50);
            $table->string('email', 255);
            $table->integer('phone');
            $table->string('emergency_c_name', 255);
            $table->integer('emergency_c_phone');
            $table->text('special_request')->nullable();
            $table->string('hear_about', 255);
            $table->boolean('newsletter')->default(false);
            $table->boolean('travel_info')->default(false);
            $table->boolean('terms_n_cons')->default(false);
            $table->boolean('cabin_conf_accp')->default(false);
            $table->boolean('single_t_agreement')->default(false);
            $table->decimal('passenger_allocated_cost', 10, 2);
            $table->decimal('passenger_balance', 10, 2);
            $table->boolean('was_on_board')->default(false);
        });

        // Create the 'fees' table
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('passengers');
            $table->string('type', 50)->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps(0);
        });

        // Create the 'payments' table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('passengers');
            $table->string('BIP_ID', 50)->nullable();
            $table->string('type', 255)->nullable();
            $table->date('transaction_date')->nullable();
            $table->decimal('amount', 10, 2);
        });

        // Create the 'onboard_credit' table
        Schema::create('onboard_credit', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->foreignId('passenger_id')->constrained('passengers');
        });

        // Create the 'installments' table
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->date('due_date')->nullable();
            $table->foreignId('passenger_id')->constrained('passengers');
        });

        // Create the 'booking_addition_tokens' table
        Schema::create('booking_addition_tokens', function (Blueprint $table) {
            $table->string('email', 255)->primary();
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();
            $table->foreignId('passenger_id')->constrained('passengers');
        });

        // Create the 'email_templates' table
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->foreignId('event_id')->nullable()->constrained('events');
            $table->string('name', 255);
            $table->string('subject', 255);
            $table->text('body');
            $table->text('placeholders')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop the 'email_templates' table
        Schema::dropIfExists('email_templates');

        // Drop the 'booking_addition_tokens' table
        Schema::dropIfExists('booking_addition_tokens');

        // Drop the 'installments' table
        Schema::dropIfExists('installments');

        // Drop the 'onboard_credit' table
        Schema::dropIfExists('onboard_credit');

        // Drop the 'payments' table
        Schema::dropIfExists('payments');

        // Drop the 'fees' table
        Schema::dropIfExists('fees');

        // Drop the 'passengers' table
        Schema::dropIfExists('passengers');

        // Drop the 'membership_type' table
        Schema::dropIfExists('membership_type');

        // Drop the 'memberships' table
        Schema::dropIfExists('memberships');

        // Revert modifications to the 'password_reset_tokens' table
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Drop the 'cabin_types' table
        Schema::dropIfExists('cabin_types');

        // Drop the 'booking_comments' table
        Schema::dropIfExists('booking_comments');

        // Drop the 'booking_logs' table
        Schema::dropIfExists('booking_logs');

        // Drop the 'bookings' table
        Schema::dropIfExists('bookings');

        // Drop the 'booking_has_discounts' table
        Schema::dropIfExists('booking_has_discounts');

        // Drop the 'discounts' table
        Schema::dropIfExists('discounts');

        // Drop the 'cabin_categories' table
        Schema::dropIfExists('cabin_categories');

        // Drop the 'cruises' table
        Schema::dropIfExists('cruises');

        // Drop the 'cabins' table
        Schema::dropIfExists('cabins');

        // Drop the 'addresses' table
        Schema::dropIfExists('addresses');

        // Drop the 'user_details' table
        Schema::dropIfExists('user_details');
    }
};
