<?php

namespace App\Domain\ValueObject;

readonly class Email
{
    public const string REGEX = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/D';

    public function __construct(
        private string $email,
    ) {
        if (!preg_match(self::REGEX, $this->email)) {
            throw new \InvalidArgumentException('Неверный формат почты');
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isEqual(Email $other): bool
    {
        return $this->email === $other->email;
    }
}
