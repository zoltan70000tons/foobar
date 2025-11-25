<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_details')
            ->select('id', 'avatar')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $raw = $row->avatar;
                    $isJsonLike = is_string($raw) && strlen($raw) > 1 && ($raw[0] === '{' || $raw[0] === '[');
                    if ($isJsonLike) {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            continue;
                        }
                    }
                    $payload = [
                        'image' => $raw ?: null,
                        'badge' => [
                            'text' => \App\Models\UserDetail::DEFAULT_BADGE_TEXT,
                            'background' => \App\Models\UserDetail::DEFAULT_BADGE_BACKGROUND,
                        ], 
                    ];

                    DB::table('user_details')
                        ->where('id', $row->id)
                        ->update(['avatar' => json_encode($payload)]);
                }
            });
    }

    public function down(): void
    {
        
    }
};
