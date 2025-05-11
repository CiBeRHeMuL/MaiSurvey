<?php

namespace App\Infrastructure\Db\Dbal\Types;

use AndrewGos\ClassBuilder\ClassBuilder;
use App\Domain\Dto\Notice\NoticeContextInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\SerializationFailed;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\Uid\Uuid;
use Throwable;

class NoticeContextType extends JsonType
{
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value) && json_validate($value)) {
            return $value;
        } elseif ($value instanceof NoticeContextInterface) {
            $value = (array)$value;

            array_walk_recursive(
                $value,
                static function (mixed &$item) {
                    if ($item instanceof Uuid) {
                        $item = ['uuid' => $item->toRfc4122()];
                    }
                },
            );
            return json_encode($value, JSON_PRESERVE_ZERO_FRACTION);
        } else {
            throw SerializationFailed::new(
                $value,
                'string',
                'Value must be an instance of ' . NoticeContextInterface::class . ' class',
            );
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): NoticeContextInterface|null
    {
        if ($value === null || $value instanceof NoticeContextInterface) {
            return $value;
        }

        try {
            $value = json_decode($value, JSON_THROW_ON_ERROR);
            $builder = new ClassBuilder();
            return $builder->build(NoticeContextInterface::class, $value);
        } catch (Throwable $e) {
            throw ValueNotConvertible::new(
                $value,
                NoticeContextInterface::class,
                $e->getMessage(),
                $e,
            );
        }
    }
}
