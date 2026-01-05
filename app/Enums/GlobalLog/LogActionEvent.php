<?php

namespace App\Enums\GlobalLog;

enum LogActionEvent: string {
    case EVENT_CREATED = 'EVENT_CREATED';
    case EVENT_UPDATED = 'EVENT_UPDATED';
    case EVENT_DELETED = 'EVENT_DELETED';
}
