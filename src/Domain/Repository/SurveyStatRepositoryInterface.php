<?php

namespace App\Domain\Repository;

use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface SurveyStatRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Uuid $surveyId
     *
     * @return SurveyStat|null
     */
    public function findForSurvey(Uuid $surveyId): SurveyStat|null;
}
