<?php

namespace App\Application\UseCase\Survey;

use App\Application\Dto\Survey\GetMySurveysDto;
use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetMySurveysDto as DomainGetMySurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\User;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Service\Survey\SurveyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class GetMySurveysUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        private SurveyService $surveyService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GetMySurveysUseCase
    {
        $this->logger = $logger;
        $this->surveyService->setLogger($logger);
        return $this;
    }

    /**
     * @param User $user
     * @param GetMySurveysDto $dto
     *
     * @return DataProviderInterface<MySurvey>
     */
    public function execute(User $user, GetMySurveysDto $dto): DataProviderInterface
    {
        return $this
            ->surveyService
            ->getMy(
                $user,
                new DomainGetMySurveysDto(
                    $dto->subject_ids !== null
                        ? array_map(fn(string $id) => new Uuid($id), $dto->subject_ids)
                        : null,
                    $dto->completed,
                    $dto->limit,
                    $dto->offset,
                    $dto->sort_by,
                    SortTypeEnum::from($dto->sort_type),
                ),
            );
    }
}
