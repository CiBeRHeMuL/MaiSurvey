<?php

namespace App\Application\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class DateTime extends Constraint
{
    public const INVALID_FORMAT_ERROR = '1a9da513-2640-4f84-9b6a-4d99dcddc629';
    public const INVALID_DATE_ERROR = 'd52afa47-620d-4d99-9f08-f4d85b36e33d';
    public const INVALID_TIME_ERROR = '5e797c9d-74f7-4098-baa3-94390c447b28';

    protected const ERROR_NAMES = [
        self::INVALID_FORMAT_ERROR => 'INVALID_FORMAT_ERROR',
    ];

    public string $message = 'Значение не является корректным временем по стандарту RFC3339';

    public function __construct(?array $options = null, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
