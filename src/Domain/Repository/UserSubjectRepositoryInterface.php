<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserSubject\GetAllUserSubjectsDto;
use App\Domain\Repository\Common\RepositoryInterface;

interface UserSubjectRepositoryInterface extends RepositoryInterface
{
    /**
     * Поиск с учетом пагинации и сортировки
     *
     * @param GetAllUserSubjectsDto $dto
     *
     * @return DataProviderInterface
     */
    public function findAll(GetAllUserSubjectsDto $dto): DataProviderInterface;
}
