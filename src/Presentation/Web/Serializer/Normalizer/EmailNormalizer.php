<?php

namespace App\Presentation\Web\Serializer\Normalizer;

use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class EmailNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritDoc
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (!$object instanceof Email) {
            throw new InvalidArgumentException('Object must be an instance of ' . Email::class . '.');
        }
        return $object->getEmail();
    }

    /**
     * @inheritDoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Email;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return [Email::class => true];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Email
    {
        if (!is_string($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                'Значение должно быть строкой',
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                true,
            );
        }
        try {
            return new Email($data);
        } catch (Throwable $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                $e->getMessage(),
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                true,
                previous: $e,
            );
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, Email::class, true);
    }
}
