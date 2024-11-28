<?php

namespace App\Domain\Db\Dbal\Types;

use App\Domain\ValueObject\Email;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\StringType;

class EmailType extends StringType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string|null
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof Email) {
            return $value->getEmail();
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . Email::class . ' class',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): Email|null
    {
        if ($value instanceof Email || $value === null) {
            return $value;
        }
        try {
            return new Email($value);
        } catch (\Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                Email::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
