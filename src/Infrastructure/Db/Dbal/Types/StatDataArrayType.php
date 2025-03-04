<?php

namespace App\Infrastructure\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\SurveyStatItem\StatDataInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use stdClass;
use Throwable;

class StatDataArrayType extends JsonType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value) && json_validate($value)) {
            return $value;
        } elseif (is_array($value)) {
            $value = json_decode(json_encode($value, JSON_PRESERVE_ZERO_FRACTION), flags: JSON_PRESERVE_ZERO_FRACTION);
            foreach ($value as $val) {
                if ($val->teacher_id !== null) {
                    $tId = $val->teacher_id;
                    $val->teacher_id = new stdClass();
                    $val->teacher_id->uuid = $tId;
                }
            }
            return json_encode($value, JSON_PRESERVE_ZERO_FRACTION);
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . StatDataInterface::class . '[] type',
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
            return $builder->buildArray(StatDataInterface::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                StatDataInterface::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
