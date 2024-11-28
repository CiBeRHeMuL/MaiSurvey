<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Subject\GetAllSubjectsDto;
use App\Domain\Entity\Subject;
use App\Domain\Repository\Common\RepositoryInterface;
use Iterator;
use Symfony\Component\Uid\Uuid;

interface SubjectRepositoryInterface extends RepositoryInterface
{
    /**
     * @param GetAllSubjectsDto $dto
     *
     * @return DataProviderInterface<Subject>
     */
    public function findAll(GetAllSubjectsDto $dto): DataProviderInterface;

    /**
     * Поиск по имени.
     *
     * @param string $name
     *
     * @return Subject|null
     */
    public function findByName(string $name): Subject|null;

    /**
     * Поиск по id
     *
     * @param Uuid $id
     *
     * @return Subject|null
     */
    public function findById(Uuid $id): Subject|null;

    /**
     * Список групп по списку называний
     *
     * @param string[] $groupNames
     *
     * @return Iterator<int, Subject>
     */
    public function findByNames(array $groupNames): Iterator;
}
