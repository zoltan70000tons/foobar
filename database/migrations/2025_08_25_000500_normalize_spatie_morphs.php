<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $fqn = 'App\\Models\\User';
        $alias = 'user';

        DB::statement("UPDATE model_has_roles SET model_type = '$alias' WHERE model_type = '$fqn'");
        DB::statement("UPDATE model_has_permissions SET model_type = '$alias' WHERE model_type = '$fqn'");
    }

    public function down(): void
    {
        $fqn = 'App\\Models\\User';
        $alias = 'user';

        DB::statement("UPDATE model_has_roles SET model_type = '$fqn' WHERE model_type = '$alias'");
        DB::statement("UPDATE model_has_permissions SET model_type = '$fqn' WHERE model_type = '$alias'");
    }
};
