<?php

namespace App\Domain\Db\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Throwable;

class TextArrayType extends Type
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string|null
    {
        if ($value === null) {
            return null;
        }
        try {
            if ($platform instanceof PostgreSQLPlatform) {
                $value = array_map(
                    fn($v) => $v === null ? 'null' : "\"$v\"",
                    $value,
                );
                return '{' . implode(',', $value) . '}';
            } else {
                return implode(',', $value);
            }
        } catch (Throwable $e) {
            throw SerializationFailed::new(
                $value,
                'text[]',
                'Value must be an array of strings',
                $e,
            );
        }
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return string[]|null
     * @throws ValueNotConvertible
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array|null
    {
        if ($value === null) {
            return null;
        }
        try {
            if ($platform instanceof PostgreSQLPlatform) {
                return explode(
                    ',',
                    substr($value, 1, strlen($value) - 2),
                );
            }
            return explode(',', $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                'string[]',
                $e,
            );
        }
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return ['text[]', '_text'];
        } else {
            return [Types::TEXT];
        }
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return 'text[]';
        } else {
            return $platform->getStringTypeDeclarationSQL($column);
        }
    }
}
