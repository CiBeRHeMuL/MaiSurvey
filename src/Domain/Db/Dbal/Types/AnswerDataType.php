<?php

namespace App\Domain\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\SurveyItemAnswer\AnswerDataInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use Throwable;

class AnswerDataType extends JsonType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value) && json_validate($value)) {
            return $value;
        } elseif ($value instanceof AnswerDataInterface) {
            return json_encode($value);
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . AnswerDataInterface::class . ' class',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): AnswerDataInterface|null
    {
        if ($value === null || $value instanceof AnswerDataInterface) {
            return $value;
        }

        try {
            $value = json_decode($value, JSON_THROW_ON_ERROR);
            $builder = new ClassBuilder();
            return $builder->build(AnswerDataInterface::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                AnswerDataInterface::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
