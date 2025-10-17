<?php

namespace App\Support;

use App\Enums\GlobalLog\{
    LogActionBooking,
    LogActionCabin,
    LogActionCustomer,
    LogActionEvent,
    LogActionUser
};

class LogActions
{
    public static function all(): array
    {
        return [
            'booking' => array_column(LogActionBooking::cases(), 'value'),
            'cabin'   => array_column(LogActionCabin::cases(), 'value'),
            'customer'=> array_column(LogActionCustomer::cases(), 'value'),
            'event'   => array_column(LogActionEvent::cases(), 'value'),
            'user'    => array_column(LogActionUser::cases(), 'value'),
        ];
    }
}
