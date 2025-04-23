<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\Entity\MyTeacherSubject;
use App\Domain\Entity\Semester;
use App\Domain\Entity\Subject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Repository\TeacherSubjectRepositoryInterface;
use ArrayIterator;
use Iterator;
use Qstart\Db\QueryBuilder\DML\Expression\InExpr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class TeacherSubjectRepository extends Common\AbstractRepository implements TeacherSubjectRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findMy(User $user, GetMyTeacherSubjectsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['mts.*', 'name' => 's.name'])
            ->from(['mts' => $this->getClassTable(MyTeacherSubject::class)])
            ->innerJoin(
                ['ts' => $this->getClassTable(TeacherSubject::class)],
                'mts.teacher_subject_id = ts.id',
            )
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id',
            )
            ->where([
                'ts.teacher_id' => $user->getId()->toRfc4122(),
            ]);
        if ($dto->getSubjectIds() !== null) {
            $q->andWhere([
                'ts.subject_id' => array_map(
                    fn(Uuid $id) => $id->toRfc4122(),
                    $dto->getSubjectIds(),
                ),
            ]);
        }
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                MyTeacherSubject::class,
                [
                    'teacherSubject',
                    'teacherSubject.teacher',
                    'teacherSubject.teacher.data',
                    'teacherSubject.teacher.data.group',
                    'teacherSubject.subject',
                ],
                new LimitOffset(
                    $dto->getLimit(),
                    $dto->getOffset(),
                ),
                new DataSort([
                    new SortColumn(
                        match ($dto->getSortBy()) {
                            'name' => 'name',
                            default => 'ts.' . $dto->getSortBy(),
                        },
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    /**
     * @inheritDoc
     */
    public function findAll(GetAllTeacherSubjectsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['ts.*', 'name' => 's.name'])
            ->from(['ts' => $this->getClassTable(TeacherSubject::class)])
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id',
            );
        if ($dto->getSubjectIds() !== null) {
            $q->andWhere([
                'ts.subject_id' => array_map(
                    fn(Uuid $id) => $id->toRfc4122(),
                    $dto->getSubjectIds(),
                ),
            ]);
        }
        if ($dto->getTeacherIds() !== null) {
            $q->andWhere([
                'ts.subject_id' => array_map(
                    fn(Uuid $id) => $id->toRfc4122(),
                    $dto->getTeacherIds(),
                ),
            ]);
        }
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                TeacherSubject::class,
                ['teacher', 'teacher.data', 'teacher.data.group', 'subject'],
                new LimitOffset(
                    $dto->getLimit(),
                    $dto->getOffset(),
                ),
                new DataSort([
                    new SortColumn(
                        match ($dto->getSortBy()) {
                            'name' => 'name',
                            default => 'ts.' . $dto->getSortBy(),
                        },
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    /**
     * @inheritDoc
     */
    public function findAllByIndexes(array $indexes): Iterator
    {
        if ($indexes === []) {
            return new ArrayIterator([]);
        }
        $q = Query::select()
            ->from(['ts' => $this->getClassTable(TeacherSubject::class)])
            ->where(new InExpr(
                ['teacher_id', 'subject_id', 'type'],
                array_map(
                    fn(GetTSByIndexDto $dto) => [
                        'teacher_id' => $dto->getTeacherId()->toRfc4122(),
                        'subject_id' => $dto->getSubjectId()->toRfc4122(),
                        'type' => $dto->getType()->value,
                    ],
                    $indexes,
                ),
            ));
        return new ArrayIterator(
            $this->findAllByQuery(
                $q,
                TeacherSubject::class,
                ['teacher', 'subject', 'subject.semester'],
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function findAllByRawIndexes(array $indexes): Iterator
    {
        if ($indexes === []) {
            return new ArrayIterator([]);
        }
        $q = Query::select()
            ->select(['ts.*'])
            ->from(['ts' => $this->getClassTable(TeacherSubject::class)])
            ->innerJoin(
                ['u' => $this->getClassTable(User::class)],
                'u.id = ts.teacher_id',
            )
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id',
            )
            ->innerJoin(
                ['sem' => $this->getClassTable(Semester::class)],
                'sem.id = s.semester_id'
            )
            ->where(new InExpr(
                ['lower(u.email)', 'lower(s.name)', 'ts.type', 'sem.year', 'sem.spring'],
                array_map(
                    fn(GetTSByIndexRawDto $dto) => [
                        'u.email' => $dto->getTeacherEmail()->getEmail(),
                        's.name' => $dto->getSubjectName(),
                        'ts.type' => $dto->getType()->value,
                        'sem.year' => $dto->getSemesterDto()->getYear(),
                        'sem.spring' => $dto->getSemesterDto()->isSpring(),
                    ],
                    $indexes,
                ),
            ));
        return new ArrayIterator(
            $this->findAllByQuery(
                $q,
                TeacherSubject::class,
                ['teacher', 'subject'],
            ),
        );
    }
}
