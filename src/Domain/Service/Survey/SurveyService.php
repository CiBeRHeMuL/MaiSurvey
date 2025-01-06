<?php

namespace App\Domain\Service\Survey;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\User;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\SurveyRepositoryInterface;
use App\Domain\Service\Template\TemplateService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;

class SurveyService
{
    public const array GET_MY_SORT = ['name', 'completed', 'created_at'];

    private LoggerInterface $logger;

    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
        private TemplateService $templateService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SurveyService
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param User $user
     * @param GetMySurveysDto $dto
     *
     * @return DataProviderInterface<MySurvey>
     */
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

        return new ProjectionAwareDataProvider(
            $this
                ->surveyRepository
                ->findMy($user, $dto),
            $this->prepareMySurvey(...),
        );
    }

    public function getMyById(User $user, GetMySurveyByIdDto $dto): MySurvey|null
    {
        $survey = $this
            ->surveyRepository
            ->findMyById($user, $dto);
        return $survey !== null ? $this->prepareMySurvey($survey) : null;
    }

    private function prepareMySurvey(MySurvey $survey): MySurvey
    {
        $items = $survey->getMyItems()->toArray();
        array_walk($items, $this->templateService->putTsIntoMySurveyItem(...));
        return $survey;
    }
}
