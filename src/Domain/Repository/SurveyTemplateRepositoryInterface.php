<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\SurveyTemplate\GetAllSurveyTemplatesDto;
use App\Domain\Entity\SurveyTemplate;
use Symfony\Component\Uid\Uuid;

interface SurveyTemplateRepositoryInterface extends Common\RepositoryInterface
{
    /**
     * @param GetAllSurveyTemplatesDto $dto
     *
     * @return DataProviderInterface<SurveyTemplate>
     */
    public function findAll(GetAllSurveyTemplatesDto $dto): DataProviderInterface;

    public function findById(Uuid $id): SurveyTemplate|null;
}
