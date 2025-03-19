<?php

namespace App\Infrastructure\Repository;

use App\Domain\Dto\SurveyStatItem\CommentStatData;
use App\Domain\Entity\MySurveyItem;
use App\Domain\Entity\SurveyItem;
use App\Domain\Entity\SurveyItemAnswer;
use App\Domain\Entity\SurveyStatItem;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\UserData;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Repository\SurveyStatItemRepositoryInterface;
use App\Infrastructure\Db\Expr\AnyValueFunc;
use App\Infrastructure\Db\Expr\ArrayFromJsonbFunc;
use App\Infrastructure\Db\Expr\ArrayPositionFunc;
use App\Infrastructure\Db\Expr\CaseExpr;
use App\Infrastructure\Db\Expr\CastExpr;
use App\Infrastructure\Db\Expr\CoalesceFunc;
use App\Infrastructure\Db\Expr\FullNameExpr;
use App\Infrastructure\Db\Expr\JsonbAggFunc;
use App\Infrastructure\Db\Expr\JsonbArrayElementsTextFunc;
use App\Infrastructure\Db\Expr\JsonbBuildObjectFunc;
use App\Infrastructure\Db\Expr\JsonbPathQueryArrayFunc;
use App\Infrastructure\Db\Expr\JsonbPathQueryFunc;
use App\Infrastructure\Db\Expr\SumFunc;
use App\Infrastructure\Service\SurveyStat\CommentsSummaryGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Qstart\Db\QueryBuilder\DML\Expression\Expr;
use Qstart\Db\QueryBuilder\DML\Query\SelectQuery;
use Qstart\Db\QueryBuilder\Query;
use Symfony\Component\Uid\Uuid;

class SurveyStatItemRepository extends Common\AbstractRepository implements SurveyStatItemRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        private CommentsSummaryGeneratorInterface $commentsSummaryGenerator,
    ) {
        $this->commentsSummaryGenerator->setLogger($logger);
        parent::__construct($entityManager, $logger);
    }

    /**
     * @inheritDoc
     */
    public function findStatFromSurvey(Uuid $surveyId): array
    {
        return $this->findStatFromSurveys([$surveyId]);
    }

    public function findStatFromSurveys(?array $surveyIds = null): array
    {
        $q = $this->getFromSurveyQuery($surveyIds);
        /** @var SurveyStatItem[] $allItems */
        $allItems = $this
            ->findAllByQuery(
                $q,
                SurveyStatItem::class,
            );
        foreach ($allItems as $item) {
            if ($item->getType() === SurveyItemTypeEnum::Comment) {
                $stats = $item->getStats();
                foreach ($stats as &$stat) {
                    if ($stat instanceof CommentStatData) {
                        $comments = json_decode($stat->getSummary());
                        $summary = $this->commentsSummaryGenerator->generateSummary($comments);
                        $stat = new CommentStatData(
                            $stat->getType(),
                            $stat->getTeacherId(),
                            $stat->getTeacherName(),
                            $stat->getCompletedCount(),
                            $stat->getAvailableCount(),
                            $summary,
                        );
                    }
                }
                $item->setStats($stats);
            }
        }
        return $allItems;
    }

    public function createOrUpdate(array $items): void
    {
        if ($items === []) {
            return;
        }
        $this->executeQuery(
            Query::delete()
                ->from($this->getClassTable(SurveyStatItem::class))
                ->where([
                    'id' => array_map(
                        fn(SurveyStatItem $item) => $item->getId()->toRfc4122(),
                        $items,
                    ),
                ]),
        );
        $this->createMulti($items);
    }

    /**
     * @param Uuid[]|null $surveyIds
     *
     * @return SelectQuery
     */
    private function getFromSurveyQuery(array|null $surveyIds): SelectQuery
    {
        $surveyIds = $surveyIds !== null
            ? array_map(fn(Uuid $i) => $i->toRfc4122(), $surveyIds)
            : null;
        return Query::select()
            ->select([
                't2.id',
                't2.survey_id',
                'available_count' => new AnyValueFunc(new Expr('t2.all_available_count')),
                'completed_count' => new AnyValueFunc(new Expr('t2.all_completed_count')),
                'stats' => new JsonbAggFunc(new Expr('t2.data')),
                'type' => 't2.type',
                'position' => new AnyValueFunc(new Expr('t2.position')),
            ])
            ->from([
                't2' => Query::select()
                    ->select([
                        'data' => new CaseExpr([
                            [
                                new Expr(
                                    't.type = :dc_ch OR t.type = :dc_mch',
                                    [
                                        ':dc_ch' => SurveyItemTypeEnum::Choice->value,
                                        ':dc_mch' => SurveyItemTypeEnum::MultiChoice->value,
                                    ],
                                ),
                                new JsonbBuildObjectFunc([
                                    'available_count',
                                    new AnyValueFunc(new Expr('t.teacher_available_count')),
                                    'completed_count',
                                    new AnyValueFunc(new Expr('t.teacher_completed_count')),
                                    'counts',
                                    new JsonbAggFunc(
                                        new JsonbBuildObjectFunc([
                                            'choice',
                                            new Expr('t.answer'),
                                            'count',
                                            new Expr('t.answer_completed_count'),
                                        ]),
                                        new ArrayPositionFunc(
                                            new ArrayFromJsonbFunc(
                                                new JsonbPathQueryArrayFunc(
                                                    new Expr('t.data'),
                                                    '$.choices[*].value',
                                                ),
                                            ),
                                            new Expr('to_jsonb(t.answer)'),
                                        ),
                                        new Expr(
                                            't.type = :dc_ch OR t.type = :dc_mch',
                                            [
                                                ':dc_ch_1' => SurveyItemTypeEnum::Choice->value,
                                                ':dc_mch_1' => SurveyItemTypeEnum::MultiChoice->value,
                                            ],
                                        ),
                                    ),
                                    'type',
                                    new Expr('t.type'),
                                    'teacher_name',
                                    new AnyValueFunc(new Expr('t.teacher_name')),
                                    'teacher_id',
                                    new CaseExpr(
                                        [
                                            [
                                                new Expr('t.teacher_id IS NOT NULL'),
                                                new JsonbBuildObjectFunc([
                                                    'uuid',
                                                    new Expr('t.teacher_id'),
                                                ]),
                                            ],
                                        ],
                                        else: new Expr('NULL::jsonb'),
                                    ),
                                ]),
                            ],
                            [
                                new Expr(
                                    't.type = :dc_r',
                                    [':dc_r' => SurveyItemTypeEnum::Rating->value],
                                ),
                                new JsonbBuildObjectFunc([
                                    'available_count',
                                    new AnyValueFunc(new Expr('t.teacher_available_count')),
                                    'completed_count',
                                    new AnyValueFunc(new Expr('t.teacher_completed_count')),
                                    'counts',
                                    new JsonbAggFunc(
                                        new JsonbBuildObjectFunc([
                                            'rating',
                                            new Expr('t.answer::integer'),
                                            'count',
                                            new Expr('t.answer_completed_count'),
                                        ]),
                                        new Expr('t.answer ASC'),
                                        new Expr(
                                            't.type = :dc_r',
                                            [':dc_r' => SurveyItemTypeEnum::Rating->value],
                                        ),
                                    ),
                                    'average',
                                    new CaseExpr(
                                        [[0, new Expr('0.00::numeric(3, 2)')]],
                                        new SumFunc(new Expr('t.answer_completed_count')),
                                        new Expr(
                                            'round(sum(t.answer::integer * t.answer_completed_count)'
                                            . ' FILTER ( WHERE t.type = :dc_r ) / sum(t.answer_completed_count), 2)',
                                            [':dc_r' => SurveyItemTypeEnum::Rating->value],
                                        )
                                    ),
                                    'type',
                                    new Expr('t.type'),
                                    'teacher_name',
                                    new AnyValueFunc(new Expr('t.teacher_name')),
                                    'teacher_id',
                                    new CaseExpr(
                                        [
                                            [
                                                new Expr('t.teacher_id IS NOT NULL'),
                                                new JsonbBuildObjectFunc([
                                                    'uuid',
                                                    new Expr('t.teacher_id'),
                                                ]),
                                            ],
                                        ],
                                        else: new Expr('NULL::jsonb'),
                                    ),
                                ]),
                            ],
                            [
                                new Expr(
                                    't.type = :dc_com',
                                    [':dc_com' => SurveyItemTypeEnum::Comment->value],
                                ),
                                new JsonbBuildObjectFunc([
                                    'available_count',
                                    new AnyValueFunc(new Expr('t.teacher_available_count')),
                                    'completed_count',
                                    new AnyValueFunc(new Expr('t.teacher_completed_count')),
                                    'summary',
                                    new CastExpr(
                                        new CoalesceFunc(
                                            new JsonbAggFunc(
                                                new Expr('t.answer'),
                                                filterWhere: new Expr(
                                                    't.type = :dc_com',
                                                    [':dc_com' => SurveyItemTypeEnum::Comment->value],
                                                ),
                                            ),
                                            new Expr("'[]'::jsonb"),
                                        ),
                                        'text',
                                    ),
                                    'type',
                                    new Expr('t.type'),
                                    'teacher_name',
                                    new AnyValueFunc(new Expr('t.teacher_name')),
                                    'teacher_id',
                                    new CaseExpr(
                                        [
                                            [
                                                new Expr('t.teacher_id IS NOT NULL'),
                                                new JsonbBuildObjectFunc([
                                                    'uuid',
                                                    new Expr('t.teacher_id'),
                                                ]),
                                            ],
                                        ],
                                        else: new Expr('NULL::jsonb'),
                                    ),
                                ]),
                            ],
                        ]),
                        't.id',
                        't.type',
                        't.teacher_id',
                        't.survey_id',
                        'position' => new AnyValueFunc(new Expr('t.position')),
                        'all_available_count' => new AnyValueFunc(new Expr('t.all_available_count')),
                        'all_completed_count' => new AnyValueFunc(new Expr('t.all_completed_count')),
                    ])
                    ->from([
                        't' => Query::select()
                            ->select([
                                'teacher_available_count' => new CoalesceFunc(new Expr('teacher_msi.count'), 0),
                                'teacher_completed_count' => new CoalesceFunc(new Expr('teacher_sia.count'), 0),
                                'answer_completed_count' => new CoalesceFunc(new Expr('sia.count'), 0),
                                'all_available_count' => new CoalesceFunc(new Expr('all_msi.count'), 0),
                                'all_completed_count' => new CoalesceFunc(new Expr('all_sia.count'), 0),
                                'si.id',
                                'ts.teacher_id',
                                'si.type',
                                'si.survey_id',
                                'si.position',
                                'si.data',
                                'answer' => new CaseExpr(
                                    [[SurveyItemTypeEnum::Comment->value, new Expr('sia.answer')]],
                                    new Expr('si.type'),
                                    new Expr('si.answer'),
                                ),
                                'teacher_name' => new CaseExpr([
                                    [
                                        new Expr('teacher_msi.teacher_subject_id IS NOT NULL'),
                                        new FullNameExpr('tsud'),
                                    ],
                                ]),
                            ])
                            ->from([
                                'si' => Query::select()
                                    ->select([
                                        'si.id',
                                        'si.type',
                                        'si.survey_id',
                                        'si.position',
                                        'si.data',
                                        'answer' => new CaseExpr(
                                            [
                                                [
                                                    SurveyItemTypeEnum::Rating->value,
                                                    new Expr('r.r::text')
                                                ],
                                                [SurveyItemTypeEnum::Comment->value, ''],
                                            ],
                                            new Expr('si.type'),
                                            new Expr('c.c->>0'),
                                        ),
                                    ])
                                    ->from(['si' => $this->getClassTable(SurveyItem::class)])
                                    ->join(
                                        ['r(r)' => new Expr("generate_series((si.data ->> 'min')::integer, (si.data ->> 'max')::integer)")],
                                        ['si.type' => SurveyItemTypeEnum::Rating->value],
                                        'LEFT JOIN LATERAL',
                                    )
                                    ->join(
                                        ['c(c)' => new JsonbPathQueryFunc(new Expr('si.data'), '$.choices[*].value')],
                                        ['si.type' => [SurveyItemTypeEnum::Choice->value, SurveyItemTypeEnum::MultiChoice->value]],
                                        'LEFT JOIN LATERAL',
                                    )
                                    ->andFilterWhere(['si.survey_id' => $surveyIds]),
                            ])
                            ->leftJoin(
                                [
                                    'teacher_msi' => Query::select()
                                        ->select([
                                            'count' => new Expr('count(DISTINCT user_id)'),
                                            'id',
                                            'teacher_subject_id',
                                        ])
                                        ->from($this->getClassTable(MySurveyItem::class))
                                        ->andFilterWhere(['survey_id' => $surveyIds])
                                        ->groupBy(['id', 'teacher_subject_id'])
                                ],
                                'teacher_msi.id = si.id',
                            )
                            ->leftJoin(
                                [
                                    'sia' => Query::select()
                                        ->select([
                                            'sia.survey_item_id',
                                            'sia.teacher_subject_id',
                                            'sia.answer',
                                            'count' => new Expr('count(*)'),
                                        ])
                                        ->from([
                                            'sia' => Query::select()
                                                ->select([
                                                    'sia.survey_item_id',
                                                    'sia.teacher_subject_id',
                                                    'answer' => new CaseExpr(
                                                        [
                                                            [
                                                                SurveyItemTypeEnum::Rating->value,
                                                                new Expr("sia.answer ->> 'rating'"),
                                                            ],
                                                            [
                                                                SurveyItemTypeEnum::Choice->value,
                                                                new Expr("sia.answer ->> 'choice'"),
                                                            ],
                                                            [
                                                                SurveyItemTypeEnum::Comment->value,
                                                                new Expr("sia.answer ->> 'comment'"),
                                                            ],
                                                            [
                                                                SurveyItemTypeEnum::MultiChoice->value,
                                                                new Expr("c.c"),
                                                            ],
                                                        ],
                                                        new Expr('sia.type'),
                                                    ),
                                                ])
                                                ->from(['sia' => $this->getClassTable(SurveyItemAnswer::class)])
                                                ->join(
                                                    ['c(c)' => new JsonbArrayElementsTextFunc(new Expr("sia.answer->'choices'"))],
                                                    ['sia.type' => SurveyItemTypeEnum::MultiChoice->value],
                                                    'LEFT JOIN LATERAL',
                                                ),
                                        ])
                                        ->groupBy([
                                            'sia.survey_item_id',
                                            'sia.teacher_subject_id',
                                            'sia.answer',
                                        ]),
                                ],
                                [
                                    'AND',
                                    ['si.id' => new Expr('sia.survey_item_id')],
                                    [
                                        'OR',
                                        ['teacher_msi.teacher_subject_id' => new Expr('sia.teacher_subject_id')],
                                        [
                                            'teacher_msi.teacher_subject_id' => null,
                                            'sia.teacher_subject_id' => null,
                                        ],
                                    ],
                                    [
                                        'OR',
                                        [
                                            'AND',
                                            ['si.answer' => new Expr('sia.answer')],
                                            ['NOT', ['si.type' => SurveyItemTypeEnum::Comment->value]],
                                        ],
                                        ['si.type' => SurveyItemTypeEnum::Comment->value]
                                    ],
                                ],
                            )
                            ->leftJoin(
                                ['ts' => $this->getClassTable(TeacherSubject::class)],
                                'ts.id = teacher_msi.teacher_subject_id',
                            )
                            ->leftJoin(
                                ['tsud' => $this->getClassTable(UserData::class)],
                                'ts.teacher_id = tsud.user_id',
                            )
                            ->leftJoin(
                                [
                                    'all_msi' => Query::select()
                                        ->select([
                                            'count' => new Expr('count(DISTINCT user_id)'),
                                            'id',
                                        ])
                                        ->from($this->getClassTable(MySurveyItem::class))
//                                        ->andFilterWhere(['survey_id' => $surveyIds])
                                        ->groupBy(['id'])
                                ],
                                'all_msi.id = si.id',
                            )
                            ->leftJoin(
                                [
                                    'all_sia' => Query::select()
                                        ->select([
                                            'count' => new Expr('count(*)'),
                                            'id' => 'survey_item_id',
                                        ])
                                        ->from($this->getClassTable(SurveyItemAnswer::class))
                                        ->groupBy(['survey_item_id'])
                                ],
                                'all_sia.id = si.id',
                            )
                            ->leftJoin(
                                [
                                    'teacher_sia' => Query::select()
                                        ->select([
                                            'id' => 'survey_item_id',
                                            'teacher_subject_id',
                                            'count' => new Expr('count(*)'),
                                        ])
                                        ->from($this->getClassTable(SurveyItemAnswer::class))
                                        ->groupBy(['survey_item_id', 'teacher_subject_id'])
                                ],
                                [
                                    'teacher_sia.teacher_subject_id' => new Expr('teacher_msi.teacher_subject_id'),
                                    'teacher_sia.id' => new Expr('si.id'),
                                ],
                            )
                    ])
                    ->groupBy(['t.id', 't.survey_id', 't.type', 't.teacher_id']),
            ])
            ->groupBy(['t2.id', 't2.survey_id', 't2.type']);
    }
}
