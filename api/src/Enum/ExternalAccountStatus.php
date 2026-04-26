<?php

namespace App\Enum;

enum ExternalAccountStatus: string
{
    case Active = 'active';
    case Disabled = 'disabled';
    case Error = 'error';
}
