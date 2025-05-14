<?php

namespace App\Application\Validator\Constraints;

use App\Domain\Dto\TelegramUser\ChatId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Throwable;

final class TelegramChatIdValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TelegramChatId) {
            throw new UnexpectedTypeException($constraint, TelegramChatId::class);
        }

        if (null === $value) {
            return;
        }

        try {
            $c = new ChatId($value);
        } catch (Throwable $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
