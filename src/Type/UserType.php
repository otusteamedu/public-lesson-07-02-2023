<?php

namespace App\Type;

use MyCLabs\Enum\Enum;

/**
 * @method static self teacher()
 * @method static self student()
 * @method static self employee()
 */
final class UserType extends Enum
{
    public const teacher = 'teacher';

    public const student = 'student';

    public const employee = 'employee';
}
