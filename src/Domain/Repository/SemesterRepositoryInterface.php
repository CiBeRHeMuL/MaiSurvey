<?php

namespace App\Domain\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\Dto\Semester\GetAllSemestersDto;
use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Entity\Semester;
use App\Domain\Repository\Common\RepositoryInterface;
use Symfony\Component\Uid\Uuid;

interface SemesterRepositoryInterface extends RepositoryInterface
{
    /**
     * @param GetSemesterByIndexDto[] $indexes
     *
     * @return Semester[]
     */
    public function findAllByIndexes(array $indexes): array;

    /**
     * @param GetAllSemestersDto $dto *
     *
     * @return DataProviderInterface<Semester>
     */
    public function findAll(GetAllSemestersDto $dto): DataProviderInterface;

    public function findById(Uuid $semesterId): Semester|null;
}
