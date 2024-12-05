<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Survey\GetMySurveysDto;
use App\Domain\Entity\MySurvey;
use App\Domain\Entity\User;
use App\Domain\Repository\Common\RepositoryInterface;

interface SurveyRepositoryInterface extends RepositoryInterface
{
    /**
     * @param User $user
     * @param GetMySurveysDto $dto
     *
     * @return DataProviderInterface<MySurvey>
     */
    public function findMy(User $user, GetMySurveysDto $dto): DataProviderInterface;
}
