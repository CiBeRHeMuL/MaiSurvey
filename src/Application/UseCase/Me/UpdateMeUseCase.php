<?php

namespace App\Application\UseCase\Me;

use App\Application\Aggregate\Me;
use App\Application\Dto\Me\UpdateMeDto;
use App\Domain\Dto\Me\UpdateMeDto as DomainUpdateMeDto;
use App\Domain\Entity\User;
use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeTypeEnum;
use App\Domain\Service\User\UserService;
use Psr\Log\LoggerInterface;

class UpdateMeUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserService $userService,
        private GetMeUseCase $meUseCase,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UpdateMeUseCase
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        return $this;
    }

    public function execute(User $me, UpdateMeDto $dto): Me
    {
        $user = $this->userService->updateMe(
            $me,
            new DomainUpdateMeDto(
                $dto->first_name,
                $dto->last_name,
                $dto->patronymic,
                $dto->notices_enabled,
                array_map(NoticeTypeEnum::from(...), $dto->notice_types),
                array_map(NoticeChannelEnum::from(...), $dto->notice_channels),
            ),
        );
        return $this->meUseCase->execute($user);
    }
}
