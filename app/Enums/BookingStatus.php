<?php

namespace App\Enums;

enum BookingStatus: string
{
    case CANCELLED = 'CANCELLED';
    case NEW = 'NEW';
    case ON_HOLD = 'ON HOLD';
    case UPLOADED = 'UPLOADED';
}
