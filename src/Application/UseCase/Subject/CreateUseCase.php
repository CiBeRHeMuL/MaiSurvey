<?php

namespace App\Application\UseCase\Subject;

use App\Application\Dto\Subject\CreateSubjectDto;
use App\Domain\Dto\Subject\CreateSubjectDto as DomainCreateSubjectDto;
use App\Domain\Entity\Subject;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Service\Semester\SemesterService;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Validation\ValidationError;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class CreateUseCase
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private SubjectService $subjectService,
        private SemesterService $semesterService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): CreateUseCase
    {
        $this->logger = $logger;
        $this->subjectService->setLogger($logger);
        $this->semesterService->setLogger($logger);
        return $this;
    }

    public function execute(CreateSubjectDto $dto): Subject
    {
        $semesterId = new Uuid($dto->semester_id);
        $semester = $this->semesterService->getById($semesterId);
        if ($semester === null) {
            throw ValidationException::new([
                new ValidationError(
                    'semester_id',
                    ValidationErrorSlugEnum::NotFound->getSlug(),
                    'Семестр не найден',
                ),
            ]);
        }

        return $this
            ->subjectService
            ->create(
                new DomainCreateSubjectDto(
                    $dto->name,
                    $semester,
                ),
            );
    }
}
