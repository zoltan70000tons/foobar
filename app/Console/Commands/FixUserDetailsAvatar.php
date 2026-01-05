<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserDetail;

class FixUserDetailsAvatar extends Command {
    protected $signature = 'users:fix-avatars {--chunk=100}';
    protected $description = 'Normalize only malformed avatar entries in user_details.';

    public function handle(): int {
        $chunkSize = (int) $this->option('chunk');

        DB::table('user_details')
            ->select('id', 'avatar')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($rows) {
                foreach ($rows as $row) {
                    $raw = $row->avatar;
                    $isJsonLike = is_string($raw) && strlen($raw) > 1 && ($raw[0] === '{' || $raw[0] === '[');

                    if ($isJsonLike) {
                        $decoded = json_decode($raw, true);
                        if (
                            json_last_error() === JSON_ERROR_NONE &&
                            is_array($decoded) &&
                            array_key_exists('image', $decoded) &&
                            array_key_exists('badge', $decoded)
                        ) {
                            continue;
                        }
                    }
                    $payload = [
                        'image' => $raw ?: null,
                        'badge' => [
                            'text' => UserDetail::DEFAULT_BADGE_TEXT,
                            'background' => UserDetail::DEFAULT_BADGE_BACKGROUND,
                        ],
                    ];

                    DB::table('user_details')
                        ->where('id', $row->id)
                        ->update(['avatar' => json_encode($payload)]);

                    $this->line("Fixed user_details.id = {$row->id}");
                }
            });

        $this->info('Process finished.');
        return self::SUCCESS;
    }
}
