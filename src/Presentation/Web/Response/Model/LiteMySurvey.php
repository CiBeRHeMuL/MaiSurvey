<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\MySurvey as DomainMySurvey;
use App\Domain\Entity\MySurveyItem;
use App\Domain\Helper\HArray;
use OpenApi\Attributes as OA;

readonly class LiteMySurvey
{
    /**
     * @param LiteSurvey $survey
     * @param bool $completed
     * @param string|null $completed_at
     * @param TeacherSubject[] $teacher_subjects
     */
    public function __construct(
        public LiteSurvey $survey,
        public bool $completed,
        #[OA\Property(format: 'date-time')]
        public string|null $completed_at,
        public array $teacher_subjects,
    ) {
    }

    public static function fromMySurvey(DomainMySurvey $survey): self
    {
        $teacherSubjects = array_values(
            array_filter(
                HArray::indexExtended(
                    $survey->getMyItems()->toArray(),
                    fn(MySurveyItem $i) => $i->getTeacherSubjectId()?->toRfc4122(),
                    fn(MySurveyItem $i) => $i->getTeacherSubject(),
                ),
            ),
        );

        return new self(
            LiteSurvey::fromMySurvey($survey),
            $survey->isCompleted(),
            $survey->getCompletedAt()?->format(DATE_RFC3339),
            array_map(
                TeacherSubject::fromTeacherSubject(...),
                $teacherSubjects,
            ),
        );
    }
}
