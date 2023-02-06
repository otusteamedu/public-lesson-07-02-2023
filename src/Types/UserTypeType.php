<?php

namespace App\Types;


use App\Type\UserType;

class UserTypeType extends AbstractVarcharEnumType
{
    public function getConcreteEnumType(): string
    {
        return UserType::class;
    }

    public function getName(): string
    {
        return 'user_type';
    }

    protected function getColumnLength(): ?int
    {
        return 10;
    }
}
