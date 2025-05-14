<?php

namespace App\Application\UseCase\TelegramUser;

use App\Application\Dto\TelegramUser\CreateTelegramUserDto;
use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Dto\TelegramUser\CreateTelegramUserDto as DomainCreateTelegramUserDto;
use App\Domain\Enum\ConnectTelegramResultEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\TelegramUser\TelegramUserService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateTelegramUserUseCase
{
    public function __construct(
        private TelegramUserService $telegramUserService,
        private ValidatorInterface $validator,
        private UserService $userService,
    ) {
    }

    public function execute(CreateTelegramUserDto $dto): ConnectTelegramResultEnum
    {
        $violations = $this->validator->validate($dto);
        if ($violations->count() > 0) {
            throw ValidationException::new(
                array_map(
                    fn(ConstraintViolationInterface $v) => new ValidationError(
                        $v->getPropertyPath(),
                        ValidationErrorSlugEnum::WrongField->getSlug(),
                        $v->getMessage(),
                    ),
                    iterator_to_array($violations),
                ),
            );
        }

        $user = $this->userService->getByTelegramConnectId(new Uuid($dto->telegram_connect_id));

        if ($user === null || $user->isActive() === false) {
            throw ErrorException::new('Действие запрещено', 403);
        }

        return $this->telegramUserService->create(
            new DomainCreateTelegramUserDto(
                $user,
                new ChatId($dto->chat_id),
            ),
        );
    }
}
