<?php

namespace App\Type;

use MyCLabs\Enum\Enum;

enum UserStatus : string
{
    case active = 'active';
    case suspended = 'suspended';
    case disabled = 'disabled';
}
