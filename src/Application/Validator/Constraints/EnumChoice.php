<?php

namespace App\Application\Validator\Constraints;

use App\Domain\Helper\HEnum;
use Attribute;
use Symfony\Component\Validator\Constraints\Choice;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EnumChoice extends Choice
{
    public function __construct(
        string $enum,
        bool|null $multiple = null,
        bool|null $strict = null,
        int|null $min = null,
        int|null $max = null,
        string|null $message = null,
        string|null $multipleMessage = null,
        string|null $minMessage = null,
        string|null $maxMessage = null,
        array|null $groups = null,
        mixed $payload = null,
        bool|null $match = null,
    ) {
        parent::__construct(
            callback: HEnum::choices($enum),
            multiple: $multiple,
            strict: $strict,
            min: $min,
            max: $max,
            message: $message,
            multipleMessage: $multipleMessage,
            minMessage: $minMessage,
            maxMessage: $maxMessage,
            groups: $groups,
            payload: $payload,
            match: $match,
        );
    }
}
