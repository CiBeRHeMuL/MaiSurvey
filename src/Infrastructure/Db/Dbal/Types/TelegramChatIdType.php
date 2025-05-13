<?php

namespace App\Infrastructure\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\TelegramUser\ChatId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\StringType;
use Throwable;

class TelegramChatIdType extends StringType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        } elseif ($value instanceof ChatId) {
            return $value->getId();
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . ChatId::class . ' class',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ChatId|null
    {
        if ($value === null || $value instanceof ChatId) {
            return $value;
        }

        try {
            $builder = new ClassBuilder();
            return $builder->build(ChatId::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                ChatId::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
