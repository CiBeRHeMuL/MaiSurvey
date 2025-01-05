<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetTeacherSubjectByIndexDto;
use App\Domain\Entity\MyTeacherSubject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use Iterator;

interface TeacherSubjectRepositoryInterface extends Common\RepositoryInterface
{
    /**
     * @param User $user
     * @param GetMyTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<MyTeacherSubject>
     */
    public function findMy(User $user, GetMyTeacherSubjectsDto $dto): DataProviderInterface;

    /**
     * @param GetAllTeacherSubjectsDto $dto
     *
     * @return DataProviderInterface<TeacherSubject>
     */
    public function findAll(GetAllTeacherSubjectsDto $dto): DataProviderInterface;

    /**
     * @param GetTeacherSubjectByIndexDto[] $indexes
     *
     * @return Iterator<int, TeacherSubject>
     */
    public function findAllByIndexes(array $indexes): Iterator;
}
