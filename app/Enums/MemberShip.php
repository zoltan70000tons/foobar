<?php

namespace App\Enums;

enum MemberShip: string {
    case MEMBERSHIP_SILVER = 'SILVER';
    case MEMBERSHIP_SILVER_PLUS = 'SILVER+';
    case MEMBERSHIP_GOLD = 'GOLD';
    case MEMBERSHIP_GOLD_PLUS = 'GOLD+';
    case MEMBERSHIP_BLACK = 'BLACK';
    case MEMBERSHIP_BLACK_PLUS = 'BLACK+';
}