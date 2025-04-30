<?php

namespace App\Infrastructure\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\SurveyStat\CountsByGroup;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use Throwable;

class CountsByGroupArrayType extends JsonType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value) && json_validate($value)) {
            return $value;
        } elseif (is_array($value)) {
            return json_encode($value, JSON_PRESERVE_ZERO_FRACTION);
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . CountsByGroup::class . '[] type',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array|null
    {
        if ($value === null || is_array($value)) {
            return $value;
        }

        try {
            $value = json_decode($value, JSON_THROW_ON_ERROR);
            $builder = new ClassBuilder();
            return $builder->buildArray(CountsByGroup::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                CountsByGroup::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
