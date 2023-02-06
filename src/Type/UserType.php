<?php

namespace App\Type;

use MyCLabs\Enum\Enum;

enum UserType : string
{
    case teacher = 'teacher';
    case student = 'student';
    case employee = 'employee';
}
