<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        DB::statement(
            <<<'SQL'
            CREATE TABLE IF NOT EXISTS taggings (
              tag_id UUID NOT NULL,
              entity_type TEXT NOT NULL,  -- 'booking' | 'cabin' | 'customer' | 'user' | (others)
              entity_id TEXT NOT NULL,    -- mix: INT for booking/cabin, UUID for customer/user
              created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
              PRIMARY KEY (tag_id, entity_type, entity_id),
              CONSTRAINT taggings_tag_fk FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            );
            SQL
            ,
        );

        DB::statement('CREATE INDEX IF NOT EXISTS taggings_by_entity ON taggings (entity_type, entity_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS taggings_by_tag    ON taggings (tag_id)');

        DB::statement(
            "CREATE INDEX IF NOT EXISTS taggings_booking_idx ON taggings ((entity_id::bigint)) WHERE entity_type = 'booking'",
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS taggings_cabin_idx   ON taggings ((entity_id::bigint)) WHERE entity_type = 'cabin'",
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS taggings_customer_idx ON taggings ((entity_id::uuid)) WHERE entity_type = 'customer'",
        );
        DB::statement(
            "CREATE INDEX IF NOT EXISTS taggings_user_idx     ON taggings ((entity_id::uuid)) WHERE entity_type = 'user'",
        );

        DB::unprepared(
            <<<'SQL'
            CREATE OR REPLACE FUNCTION taggings_type_guard()
            RETURNS TRIGGER AS $$
            DECLARE
              tag_type TEXT;
              et TEXT := NEW.entity_type;
              eid TEXT := NEW.entity_id;

              is_uuid BOOLEAN := eid ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
              is_int  BOOLEAN := eid ~ '^[0-9]+$';
            BEGIN
              IF et = 'App\\Models\\User' THEN
                et := 'user';
                NEW.entity_type := et;
              ELSIF et = 'App\\Models\\Booking' THEN
                et := 'booking';
                NEW.entity_type := et;
              ELSIF et = 'App\\Models\\Cabin' THEN
                et := 'cabin';
                NEW.entity_type := et;
              ELSIF et = 'App\\Models\\Customer' THEN
                et := 'customer';
                NEW.entity_type := et;
              END IF;

              IF et IN ('booking','cabin') AND NOT is_int THEN
                RAISE EXCEPTION 'entity_id must be INT for entity_type=%', et
                  USING ERRCODE = 'check_violation';
              ELSIF et IN ('customer','user') AND NOT is_uuid THEN
                RAISE EXCEPTION 'entity_id must be UUID for entity_type=%', et
                  USING ERRCODE = 'check_violation';
              END IF;

              SELECT type INTO tag_type FROM tags WHERE id = NEW.tag_id;
              IF tag_type IS NULL OR tag_type <> et THEN
                RAISE EXCEPTION 'Tag type % incompatible with entity_type %', tag_type, et
                  USING ERRCODE = 'check_violation';
              END IF;

              RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS taggings_type_guard_trg ON taggings;
            CREATE TRIGGER taggings_type_guard_trg
            BEFORE INSERT OR UPDATE ON taggings
            FOR EACH ROW EXECUTE FUNCTION taggings_type_guard();
            SQL
            ,
        );
    }

    public function down(): void {
        DB::unprepared('DROP TRIGGER IF EXISTS taggings_type_guard_trg ON taggings');
        DB::unprepared('DROP FUNCTION IF EXISTS taggings_type_guard()');
        DB::statement('DROP INDEX IF EXISTS taggings_booking_idx');
        DB::statement('DROP INDEX IF EXISTS taggings_cabin_idx');
        DB::statement('DROP INDEX IF EXISTS taggings_customer_idx');
        DB::statement('DROP INDEX IF EXISTS taggings_user_idx');
        DB::statement('DROP INDEX IF EXISTS taggings_by_entity');
        DB::statement('DROP INDEX IF EXISTS taggings_by_tag');
        Schema::dropIfExists('taggings');
    }
};
