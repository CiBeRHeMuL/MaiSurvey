<?php

namespace App\Infrastructure\Repository;

use App\Domain\DataProvider\DataProviderInterface;
use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\LimitOffset;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Domain\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\Subject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Repository\StudentSubjectRepositoryInterface;
use App\Infrastructure\Db\Expr\SSIntersectionExpr;
use App\Infrastructure\Db\Expr\SSRawIntersectionExpr;
use ArrayIterator;
use DateTimeImmutable;
use Iterator;
use Qstart\Db\QueryBuilder\DML\Expression\BetweenExpr;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class StudentSubjectRepository extends Common\AbstractRepository implements StudentSubjectRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAll(GetAllStudentSubjectsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['us.*', 'name' => 's.name'])
            ->from(['us' => $this->getClassTable(StudentSubject::class)])
            ->innerJoin(
                ['ts' => $this->getClassTable(TeacherSubject::class)],
                'ts.id = us.teacher_subject_id',
            )
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id'
            );
        if ($dto->getUserIds() !== null) {
            $userIds = array_unique($dto->getUserIds(), SORT_REGULAR);
            $q->andWhere([
                'us.user_id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $userIds),
            ]);
        }
        if ($dto->getSubjectIds() !== null) {
            $userIds = array_unique($dto->getSubjectIds(), SORT_REGULAR);
            $q->andWhere([
                'ts.subject_id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $userIds),
            ]);
        }
        if ($dto->getTeacherIds() !== null) {
            $userIds = array_unique($dto->getTeacherIds(), SORT_REGULAR);
            $q->andWhere([
                'ts.teacher_id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $userIds),
            ]);
        }
        if ($dto->getIsActualFrom() !== null) {
            $q->andWhere(
                new Expr(
                    'us.actual_from <= :acf',
                    ['acf' => $dto->getIsActualFrom()->format(DATE_RFC3339)],
                ),
            );
        }
        if ($dto->getIsActualTo() !== null) {
            $q->andWhere(
                new Expr(
                    'us.actual_to >= :act',
                    ['act' => $dto->getIsActualTo()->format(DATE_RFC3339)],
                ),
            );
        }
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                StudentSubject::class,
                ['user', 'teacherSubject', 'teacherSubject.subject', 'teacherSubject.teacher'],
                new LimitOffset(
                    $dto->getLimit(),
                    $dto->getOffset(),
                ),
                new DataSort([
                    new SortColumn(
                        match ($dto->getSortBy()) {
                            'name' => 'name',
                            default => 'us.' . $dto->getSortBy(),
                        },
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    public function findMy(User $user, GetMyStudentSubjectsDto $dto): DataProviderInterface
    {
        $q = Query::select()
            ->select(['us.*', 'name' => 's.name'])
            ->from(['us' => $this->getClassTable(StudentSubject::class)])
            ->innerJoin(
                ['ts' => $this->getClassTable(TeacherSubject::class)],
                'ts.id = us.teacher_subject_id',
            )
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id'
            )
            ->where(['us.user_id' => $user->getId()->toRfc4122()]);
        if ($dto->getSubjectIds() !== null) {
            $subjectIds = array_unique($dto->getSubjectIds(), SORT_REGULAR);
            $q->andWhere([
                'ts.subject_id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $subjectIds),
            ]);
        }
        if ($dto->getTeacherIds() !== null) {
            $teacherIds = array_unique($dto->getTeacherIds(), SORT_REGULAR);
            $q->andWhere([
                'ts.teacher_id' => array_map(fn(Uuid $id) => $id->toRfc4122(), $teacherIds),
            ]);
        }
        if ($dto->getActual() !== null) {
            $q->andWhere(
                new BetweenExpr(
                    (new DateTimeImmutable())->format(DATE_RFC3339),
                    'us.actual_from',
                    'us.actual_to',
                    $dto->getActual(),
                ),
            );
        }
        return $this
            ->findWithLazyBatchedProvider(
                $q,
                StudentSubject::class,
                ['user', 'teacherSubject', 'teacherSubject.subject', 'teacherSubject.teacher'],
                new LimitOffset(
                    $dto->getLimit(),
                    $dto->getOffset(),
                ),
                new DataSort([
                    new SortColumn(
                        match ($dto->getSortBy()) {
                            'name' => 'name',
                            default => 'us.' . $dto->getSortBy(),
                        },
                        $dto->getSortBy(),
                        $dto->getSortType()->getPhpSort(),
                    ),
                ]),
            );
    }

    public function findAllByIntersections(array $intersections): Iterator
    {
        if ($intersections === []) {
            return new ArrayIterator([]);
        }
        $q = Query::select()
            ->from(['ss' => $this->getClassTable(StudentSubject::class)]);
        foreach ($intersections as $index) {
            $q->andWhere(new SSIntersectionExpr($index, 'ss'));
        }
        return new ArrayIterator(
            $this->findAllByQuery(
                $q,
                StudentSubject::class,
            ),
        );
    }

    public function findAllByRawIntersections(array $intersections): Iterator
    {
        if ($intersections === []) {
            return new ArrayIterator([]);
        }
        $q = Query::select()
            ->select(['ss.*'])
            ->from(['ss' => $this->getClassTable(StudentSubject::class)])
            ->innerJoin(
                ['ts' => $this->getClassTable(TeacherSubject::class)],
                'ts.id = ss.teacher_subject_id',
            )
            ->innerJoin(
                ['su' => $this->getClassTable(User::class)],
                'su.id = ss.user_id',
            )
            ->innerJoin(
                ['tu' => $this->getClassTable(User::class)],
                'tu.id = ts.teacher_id',
            )
            ->innerJoin(
                ['s' => $this->getClassTable(Subject::class)],
                's.id = ts.subject_id',
            );
        foreach ($intersections as $index) {
            $q->andWhere(new SSRawIntersectionExpr(
                $index,
                'ss',
                'ts',
                'su',
                'tu',
                's',
            ));
        }
        return new ArrayIterator(
            $this->findAllByQuery(
                $q,
                StudentSubject::class,
                ['teacherSubject', 'user', 'teacherSubject.teacher', 'teacherSubject.subject']
            ),
        );
    }
}
