<?php

namespace App\Type;

use MyCLabs\Enum\Enum;

/**
 * @method static self active()
 * @method static self suspended()
 * @method static self disabled()
 */
final class UserStatus extends Enum
{
    public const active = 'active';

    public const suspended = 'suspended';

    public const disabled = 'disabled';
}
