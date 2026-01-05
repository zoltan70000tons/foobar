<?php

namespace App\Enums;

enum Gender: string {
    case MALE = 'M';
    case FEMALE = 'F';
    case OTHER = 'O';

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
