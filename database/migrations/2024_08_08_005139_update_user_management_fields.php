<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Modify the 'users' table
        Schema::table('users', function (Blueprint $table) {
            $table->string('survivor_number', 9)->unique()->after('id');
            $table->string('username', 50)->unique()->after('id');
            $table->string('email', 255)->nullable(false)->change();
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('password', 255)->nullable(false)->change();
            $table->string('remember_token', 100)->nullable()->change();

            // Drop not needed columns
            $table->dropUnique(['email']); // Drop the unique index on the 'email' column
            $table->dropUnique(['name']);
            $table->dropColumn(['name']);
            $table->dropColumn('zipcode');
            $table->dropColumn('state');
            $table->dropColumn('city');
            $table->dropColumn('lastname');
            $table->dropColumn('middlename');
        });

        // Modify the 'personal_access_tokens' table
        Schema::table('personal_access_tokens', function (Blueprint $table) {

            // Create relation to users table
            $table->foreignId('user_id')->constrained('users')->change();
        });

        // Modify the 'events' table
        Schema::table('events', function (Blueprint $table) {
            $table->string('name', 255)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->string('address', 255)->nullable(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('one_day_event')->default(false);
            $table->date('sales_period_start_date')->nullable();
            $table->date('sales_period_end_date')->nullable();
            $table->string('status', 50)->nullable(false);
            $table->foreignId('organization_id')->constrained('organization')->nullable(false)->change();
        });
    }

    public function down()
    {
        // Revert modifications to the 'users' table
        Schema::table('users', function (Blueprint $table) {
            // Drop the newly added columns
            $table->dropColumn('survivor_number');
            $table->dropColumn('username');

            // Revert column modifications
            $table->string('email', 255)->nullable()->change();
            $table->timestamp('email_verified_at')->nullable(false)->change();
            $table->string('password', 255)->nullable()->change();
            $table->string('remember_token', 100)->nullable(false)->change();

            // Re-add dropped columns
            $table->string('name')->unique()->after('id');
            $table->string('zipcode')->nullable()->after('name');
            $table->string('state')->nullable()->after('zipcode');
            $table->string('city')->nullable()->after('state');
            $table->string('lastname')->nullable()->after('city');
            $table->string('middlename')->nullable()->after('lastname');

            // Re-add unique indexes
            $table->unique('email');
        });

        // Revert modifications to the 'personal_access_tokens' table
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Drop the foreign key and revert to the previous column type
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->change();  // Assuming this was the original type
        });

        // Revert modifications to the 'events' table
        Schema::table('events', function (Blueprint $table) {
            // Revert the column changes
            $table->string('name', 255)->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->dropColumn('address'); // Remove the added 'address' column
            $table->dropColumn('start_date'); // Remove the added 'start_date' column
            $table->dropColumn('end_date'); // Remove the added 'end_date' column
            $table->dropColumn('one_day_event'); // Remove the added 'one_day_event' column
            $table->dropColumn('sales_period_start_date'); // Remove the added 'sales_period_start_date' column
            $table->dropColumn('sales_period_end_date'); // Remove the added 'sales_period_end_date' column
            $table->dropColumn('status'); // Remove the added 'status' column
            $table->dropForeign(['organization_id']);
            $table->unsignedBigInteger('organization_id')->change();  // Assuming this was the original type
        });
    }
};
