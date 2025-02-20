<?php

namespace App\Enums;

enum EventStatus: string
{
    case PRE_SALE = 'PRE-SALE';
    case PUBLIC = 'PUBLIC';
    case CLOSED = 'CLOSED';
    case DRAFT = 'DRAFT';

    /**
     * Labels for each status.
     */
    public static function labels(): array
    {
        return [
            self::PRE_SALE->value => 'PRE-SALE',
            self::PUBLIC->value => 'PUBLIC',
            self::CLOSED->value => 'CLOSED',
            self::DRAFT->value => 'DRAFT',
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