<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\UserSubject\GetAllUserSubjectsDto;
use App\Domain\Dto\UserSubject\GetMyUserSubjectsDto;
use App\Domain\Entity\User;
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

    /**
     * Поиск моих предметов с пагинацией и сортировкой
     *
     * @param User $user
     * @param GetMyUserSubjectsDto $dto
     *
     * @return DataProviderInterface
     */
    public function findMy(User $user, GetMyUserSubjectsDto $dto): DataProviderInterface;
}
