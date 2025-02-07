<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetSSByIndexDto;
use App\Domain\Dto\StudentSubject\GetSSByIndexRawDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\User;
use App\Domain\Repository\Common\RepositoryInterface;
use Iterator;

interface StudentSubjectRepositoryInterface extends RepositoryInterface
{
    /**
     * Поиск с учетом пагинации и сортировки
     *
     * @param GetAllStudentSubjectsDto $dto
     *
     * @return DataProviderInterface
     */
    public function findAll(GetAllStudentSubjectsDto $dto): DataProviderInterface;

    /**
     * Поиск моих предметов с пагинацией и сортировкой
     *
     * @param User $user
     * @param GetMyStudentSubjectsDto $dto
     *
     * @return DataProviderInterface
     */
    public function findMy(User $user, GetMyStudentSubjectsDto $dto): DataProviderInterface;

    /**
     * @param GetSSByIndexDto $index
     *
     * @return StudentSubject|null
     */
    public function findByIndex(GetSSByIndexDto $index): StudentSubject|null;

    /**
     * @param GetSSByIndexRawDto[] $intersections
     *
     * @return Iterator<int, StudentSubject>
     */
    public function findAllByRawIndexes(array $intersections): Iterator;
}
