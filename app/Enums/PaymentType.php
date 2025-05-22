<?php

namespace App\Enums;

enum PaymentType: string
{
    case PAYMENT = 'PAYMENT';
    case REFUND = 'REFUND';

    public function getLabel(): string
    {
        return match($this) {
            self::PAYMENT => 'Payment',
            self::REFUND => 'Refund',
        };
    }
}
