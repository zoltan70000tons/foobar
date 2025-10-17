<?php

namespace App\Enums\GlobalLog;

enum LogActionUser: string
{
    case LOGIN = 'LOGIN';
    case LOGOUT = 'LOGOUT';
}
