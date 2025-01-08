<?php

namespace App\Infrastructure\Repository;

use App\Domain\Dto\CompletedSurvey\GetCompletedSurveyByIndexDto;
use App\Domain\Entity\CompletedSurvey;
use App\Domain\Repository\CompletedSurveyRepositoryInterface;
use Qstart\Db\QueryBuilder\Query;

class CompletedSurveyRepository extends Common\AbstractRepository implements CompletedSurveyRepositoryInterface
{
    public function findByIndex(GetCompletedSurveyByIndexDto $dto): CompletedSurvey|null
    {
        $q = Query::select()
            ->from($this->getClassTable(CompletedSurvey::class))
            ->where([
                'user_id' => $dto->getUserId()->toRfc4122(),
                'survey_id' => $dto->getSurveyId()->toRfc4122(),
            ]);
        return $this->findOneByQuery(
            $q,
            CompletedSurvey::class,
            ['survey', 'user'],
        );
    }
}
