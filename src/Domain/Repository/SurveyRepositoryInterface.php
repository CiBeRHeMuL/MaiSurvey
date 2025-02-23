<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetMySurveyByIdDto;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Dto\Survey\GetSurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\Survey;
use App\Domain\Entity\User;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface SurveyRepositoryInterface extends RepositoryInterface
{
    /**
     * @param User $user
     * @param GetMySurveysDto $dto
     *
     * @return DataProviderInterface<MySurvey>
     */
    public function findMy(User $user, GetMySurveysDto $dto): DataProviderInterface;

    /**
     * @param User $user
     * @param GetMySurveyByIdDto $dto
     *
     * @return MySurvey|null
     */
    public function findMyById(User $user, GetMySurveyByIdDto $dto): MySurvey|null;

    public function findById(Uuid $id): Survey|null;

    /**
     * @param GetSurveysDto $dto
     *
     * @return DataProviderInterface<Survey>
     */
    public function findAll(GetSurveysDto $dto): DataProviderInterface;
}
