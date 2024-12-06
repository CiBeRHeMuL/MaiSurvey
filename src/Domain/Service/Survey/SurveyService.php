<?php

namespace App\Domain\Service\Survey;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyRepositoryInterface;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;

class SurveyService
{
    public const array GET_MY_SORT = ['name', 'completed', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyService
    {
        $this->logger = $logger;
        return $this;
    }

    public function getMy(User $user, GetMySurveysDto $dto): DataProviderInterface
    {
        if (!in_array($dto->getSortBy(), self::GET_MY_SORT, true)) {
            throw ValidationException::new([
                new ValidationError(
                    'name',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    sprintf('Сортировка доступна по полям: %s', implode(', ', self::GET_MY_SORT)),
                ),
            ]);
        }

        return $this
            ->surveyRepository
            ->findMy($user, $dto);
    }

    public function getMyById(User $user, GetMySurveyByIdDto $dto): MySurvey|null
    {
        return $this
            ->surveyRepository
            ->findMyById($user, $dto);
    }
}
