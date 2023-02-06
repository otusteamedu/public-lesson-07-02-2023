<?php

namespace App\Types;

use App\Type\UserStatus;

class UserStatusType extends AbstractVarcharEnumType
{
    public function getConcreteEnumType(): string
    {
        return UserStatus::class;
    }

    public function getName(): string
    {
        return 'user_status';
    }

    protected function getColumnLength(): ?int
    {
        return 10;
    }
}
