<?php

namespace App\Application\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class TelegramChatId extends Constraint
{
    public string $message = 'Значение "{{ value }}" является некорректным Telegram chat id.';

    public function __construct(
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }
}
