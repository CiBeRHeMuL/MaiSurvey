<?php

namespace App\Domain\Dto\TelegramUser;

use AndrewGos\ClassBuilder\Attribute\CanBeBuiltFromScalar;

#[CanBeBuiltFromScalar]
readonly class ChatId
{
    /**
     * @param int $id
     *
     */
    public function __construct(
        private int $id,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
