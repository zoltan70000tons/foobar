<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            if (! Schema::hasColumn('passengers', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
        });

        // Backfill existing rows
        $rows = DB::table('passengers')->whereNull('uuid')->get(['id']);
        foreach ($rows as $row) {
            DB::table('passengers')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        }
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            if (Schema::hasColumn('passengers', 'uuid')) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            }
        });
    }
};

