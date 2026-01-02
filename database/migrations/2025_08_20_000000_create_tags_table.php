<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext');

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('color', 7); // '#RRGGBB'
            $table->text('type'); // 'customer' | 'booking' | 'cabin' | ...
            $table->boolean('is_system')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement('ALTER TABLE tags ALTER COLUMN id SET DEFAULT gen_random_uuid()');

        DB::statement("ALTER TABLE tags
            ADD CONSTRAINT tags_color_hex_chk CHECK (color ~* '^#[0-9a-f]{6}$')");

        DB::statement("ALTER TABLE tags
            ADD CONSTRAINT tags_type_chk CHECK (type IN ('customer','booking','cabin'))");

        //case-insensitive
        DB::statement("CREATE UNIQUE INDEX tags_unique_name_per_type
            ON tags (type, lower(name))");

        // full text search
        DB::statement("ALTER TABLE tags
            ADD COLUMN search_tsv tsvector
            GENERATED ALWAYS AS (
              setweight(to_tsvector('simple', coalesce(name,'')), 'A') ||
              setweight(to_tsvector('simple', coalesce(description,'')), 'B')
            ) STORED");

        DB::statement('CREATE INDEX tags_search_idx ON tags USING GIN (search_tsv)');

        // Trigger updated_at
        DB::unprepared(
            <<<'SQL'
            CREATE OR REPLACE FUNCTION trg_set_timestamp()
            RETURNS TRIGGER AS $$
            BEGIN
              NEW.updated_at = now();
              RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER tags_set_timestamp
            BEFORE UPDATE ON tags
            FOR EACH ROW EXECUTE FUNCTION trg_set_timestamp();
            SQL
            ,
        );
    }

    public function down(): void {
        DB::unprepared('DROP TRIGGER IF EXISTS tags_set_timestamp ON tags');
        DB::unprepared('DROP FUNCTION IF EXISTS trg_set_timestamp()');
        Schema::dropIfExists('tags');
    }
};
