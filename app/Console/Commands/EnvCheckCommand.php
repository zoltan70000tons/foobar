<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnvCheckCommand extends Command
{
  protected $signature = 'env:check';
  protected $description = 'Report variables missing in .env compared to .env.example';

  public function handle(): int
  {
    $exampleFile = base_path('.env.example');
    $envFile     = base_path('.env');

    if (!file_exists($exampleFile) || !file_exists($envFile)) {
      $this->error('.env or .env.example not found.');
      return 1;
    }

    $exampleKeys = $this->extractKeys($exampleFile);
    $envKeys     = $this->extractKeys($envFile);

    $missing = array_diff($exampleKeys, $envKeys);

    if (empty($missing)) {
      $this->info('✅ No variables missing. .env matches .env.example.');
      return 0;
    }

    $this->warn('⚠️  Missing variables in .env:');
    foreach ($missing as $key) {
      $this->line("  - {$key}");
    }

    return 0;
  }

  private function extractKeys(string $path): array
  {
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $keys = [];

    foreach ($lines as $line) {
      $line = trim($line);

      if ($line === '' || str_starts_with($line, '#')) {
        continue;
      }

      if (preg_match('/^([A-Z0-9_]+)=/i', $line, $matches)) {
        $keys[] = $matches[1];
      }
    }

    return $keys;
  }
}
