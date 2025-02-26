<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\SurveyStat;
use App\Domain\Repository\SurveyStatRepositoryInterface;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SurveyStatRepository extends Common\AbstractRepository implements SurveyStatRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findForSurvey(Uuid $surveyId): SurveyStat|null
    {
        $q = Query::select()
            ->from(['t' => $this->getClassTable(SurveyStat::class)])
            ->where([
                'id' => $surveyId->toRfc4122(),
            ]);
        return $this
            ->findOneByQuery(
                $q,
                SurveyStat::class,
                ['items', 'survey'],
            );
    }
}
