<?php

namespace App\Domain\Repository;

use App\Domain\Dto\CompletedSurvey\GetCompletedSurveyByIndexDto;
use App\Domain\Entity\CompletedSurvey;
use App\Domain\Repository\Common\RepositoryInterface;

interface CompletedSurveyRepositoryInterface extends RepositoryInterface
{
    public function findByIndex(GetCompletedSurveyByIndexDto $dto): CompletedSurvey|null;
}
