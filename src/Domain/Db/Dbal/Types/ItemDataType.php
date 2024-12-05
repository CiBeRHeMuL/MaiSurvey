<?php

namespace App\Domain\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\SurveyItem\ItemDataInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use Throwable;

class ItemDataType extends JsonType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value) && json_validate($value)) {
            return $value;
        } elseif ($value instanceof ItemDataInterface) {
            return json_encode($value);
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . ItemDataInterface::class . ' class',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ItemDataInterface|null
    {
        if ($value === null || $value instanceof ItemDataInterface) {
            return $value;
        }

        try {
            $value = json_decode($value, JSON_THROW_ON_ERROR);
            $builder = new ClassBuilder();
            return $builder->build(ItemDataInterface::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                ItemDataInterface::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
