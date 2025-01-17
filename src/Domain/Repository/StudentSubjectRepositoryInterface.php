<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetSSByIntersectionDto;
use App\Domain\Dto\StudentSubject\GetSSByIntersectionRawDto;
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
     * @param GetSSByIntersectionDto[] $intersections
     *
     * @return Iterator<int, StudentSubject>
     */
    public function findAllByIntersections(array $intersections): Iterator;

    /**
     * @param GetSSByIntersectionRawDto[] $intersections
     *
     * @return Iterator<int, StudentSubject>
     */
    public function findAllByRawIntersections(array $intersections): Iterator;
}
