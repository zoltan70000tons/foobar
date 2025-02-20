<?php

namespace App\Enums;

enum EventStatus: string
{
    case PRE_SALE = 'pre-sale';
    case PUBLIC = 'public';
    case CLOSED = 'closed';
    case DRAFT = 'draft';

    /**
     * Labels for each status.
     */
    public static function labels(): array
    {
        return [
            self::PRE_SALE->value => 'Pre-sale',
            self::PUBLIC->value => 'Public',
            self::CLOSED->value => 'Closed',
            self::DRAFT->value => 'Draft',
        ];
    }

    /**
     * Get the label for a specific status.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? 'Unknown';
    }
}