<?php

namespace App\Domain\Dto\TelegramUser;

use AndrewGos\ClassBuilder\Attribute\CanBeBuiltFromScalar;
use InvalidArgumentException;

#[CanBeBuiltFromScalar]
readonly class ChatId
{
    /**
     * @param string $id
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private string $id,
    ) {
        if (!ctype_digit($this->id) && !preg_match('/^@[A-z\d]{5,32}$/ui', $this->id)) {
            throw new InvalidArgumentException('Invalid chat id representation');
        }
    }

    public function getId(): string
    {
        return $this->id;
    }
}
