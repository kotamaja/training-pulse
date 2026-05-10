<?php

namespace App\Security\RefreshToken;

enum RefreshTokenMode: string
{
    case Web = 'web';
    case Token = 'token';
}
