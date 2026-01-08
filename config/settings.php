<?php

return [
    'organization_id' => env('ORGANIZATION_ID', 1),
    'application_url' => env('APP_URL', 'localhost:8000'),
    'default_rows_per_page' => env('DEFAULT_ROWS', 5),
    'log_level' => env('LOG_LEVEL', 'error'),
    'session_expiration_minutes' => env('SESSION_EXPIRATION_MINUTES', 10),
    'cabin_inventory_integrity_report_recipients' => env('CABIN_INVENTORY_INTEGRITY_REPORT_RECIPIENTS', ''),
];
