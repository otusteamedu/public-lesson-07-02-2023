<?php

namespace App\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;

abstract class AbstractVarcharEnumType extends Type
{
    /**
     * @psalm-return class-string
     */
    abstract public function getConcreteEnumType(): string;

    /**
     * @param mixed $value
     *
     * @return mixed|null
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Enum) {
            return $value->getValue();
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Enum
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $concreteEnumType = $this->getConcreteEnumType();

            return new $concreteEnumType($value);
        }

        throw ConversionException::conversionFailed($value, $this->getName());
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }


    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        if ($this->getColumnLength() !== null) {
            $column['length'] = $this->getColumnLength();
        }

        return $platform->getStringTypeDeclarationSQL($column);
    }

    protected function getColumnLength(): ?int
    {
        return null;
    }
}
