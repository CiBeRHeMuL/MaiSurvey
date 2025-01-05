<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\Dto\StudentSubject\CreateStudentSubjectDto;
use App\Domain\Dto\StudentSubject\GetStudentSubjectByIntersectionDto;
use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Dto\TeacherSubject\GetTeacherSubjectByIndexDto;
use App\Domain\Entity\Subject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\DataImport\DataImportInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\TeacherSubject\TeacherSubjectService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class StudentSubjectsImporter
{
    private LoggerInterface $logger;

    public function __construct(
        private StudentSubjectService $studentSubjectService,
        private SubjectService $subjectService,
        private TeacherSubjectService $teacherSubjectService,
        private UserService $userService,
        private DataImportInterface $dataImport,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): StudentSubjectsImporter
    {
        $this->logger = $logger;
        $this->studentSubjectService->setLogger($logger);
        $this->subjectService->setLogger($logger);
        $this->teacherSubjectService->setLogger($logger);
        $this->userService->setLogger($logger);
        return $this;
    }

    public function import(ImportDto $dto): int
    {
        try {
            $this->dataImport->openFile($dto->getFile());
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e);
            throw ValidationException::new([
                new ValidationError(
                    'file',
                    ValidationErrorSlugEnum::FileNotExists->getSlug(),
                    'Не удалось открыть файл',
                ),
            ]);
        }
        $firstRow = $dto->isHeadersInFirstRow() ? 2 : 1;

        $validationErrorTemplate = 'Некорректное содержимое файла. Ошибка в строке %d: %s';

        $studentEmails = [];
        $teacherEmails = [];
        $subjectNames = [];
        $indexesData = [];
        $existingRows = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $studentEmail = $row[$dto->getStudentEmailCol()] ?? '';
            $teacherEmail = $row[$dto->getTeacherEmailCol()] ?? '';
            $subject = $row[$dto->getSubjectCol()] ?? '';
            $type = $row[$dto->getTypeCol()] ?? '';
            $actualFrom = $row[$dto->getActualFromCol()] ?? '';
            $actualTo = $row[$dto->getActualToCol()] ?? '';

            if (isset($existingRows[$studentEmail][$teacherEmail][$subject][$type])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            sprintf(
                                'повторяющийся набор данных, такой набор уже был указан в строке %d',
                                $existingRows[$studentEmail][$teacherEmail][$subject][$type],
                            ),
                        ),
                    ),
                ]);
            }
            $existingRows[$studentEmail][$teacherEmail][$subject][$type] = $k;

            $subjectNames[$k] = $subject;

            $type = TeacherSubjectTypeEnum::tryFrom($type);
            if ($type === null) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректный тип предмета',
                        ),
                    ),
                ]);
            }
            $types[$k] = $type;

            try {
                $studentEmail = new Email($studentEmail);
                $studentEmails[$k] = $studentEmail;
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректный формат почты студента',
                        ),
                    ),
                ]);
            }

            try {
                $teacherEmail = new Email($teacherEmail);
                $teacherEmails[$k] = $teacherEmail;
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректный формат почты преподавателя',
                        ),
                    ),
                ]);
            }

            try {
                $actualFrom = new DateTimeImmutable($actualFrom);
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректный формат даты',
                        ),
                    ),
                ]);
            }

            try {
                $actualTo = new DateTimeImmutable($actualTo);
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректный формат даты',
                        ),
                    ),
                ]);
            }

            $indexesData[$k] = compact('studentEmail', 'teacherEmail', 'subject', 'type', 'actualFrom', 'actualTo');
        }

        $subjects = $this
            ->subjectService
            ->getByNames($subjectNames);
        /** @var array<string, Subject> $subjects */
        $subjects = HArray::index(
            $subjects,
            fn(Subject $s) => $s->getName(),
        );

        $teachers = $this
            ->userService
            ->getAllByEmails($teacherEmails);
        /** @var array<string, User> $teachers */
        $teachers = HArray::index(
            $teachers,
            fn(User $t) => $t->getEmail()->getEmail(),
        );

        $students = $this
            ->userService
            ->getAllByEmails($studentEmails);
        /** @var array<string, User> $students */
        $students = HArray::index(
            $students,
            fn(User $t) => $t->getEmail()->getEmail(),
        );

        $teacherSubjectIndexes = [];
        foreach ($indexesData as $k => $indexesDatum) {
            [
                'subject' => $subject,
                'teacherEmail' => $teacherEmail,
                'studentEmail' => $studentEmail,
                'type' => $type,
            ] = $indexesDatum;
            $student = $students[$studentEmail->getEmail()] ?? null;
            if (!$student) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'студент не найден',
                        ),
                    ),
                ]);
            }
            $subject = $subjects[$subject] ?? null;
            if (!$subject) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'предмет не найден',
                        ),
                    ),
                ]);
            }
            $teacher = $teachers[$teacherEmail->getEmail()] ?? null;
            if (!$teacher) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'преподаватель не найден',
                        ),
                    ),
                ]);
            }

            $teacherSubjectIndexes[] = new GetTeacherSubjectByIndexDto(
                $teacher->getId(),
                $subject->getId(),
                $type,
            );
        }

        $teacherSubjects = $this
            ->teacherSubjectService
            ->getAllByIndexes($teacherSubjectIndexes);
        /** @var array<string, array<string, array<string, TeacherSubject>>> $teacherSubjects */
        $teacherSubjects = HArray::groupIndexing(
            $teacherSubjects,
            [
                fn(TeacherSubject $ts) => $ts->getSubject()->getName(),
                fn(TeacherSubject $ts) => $ts->getTeacher()->getEmail()->getEmail(),
            ],
            fn(TeacherSubject $ts) => $ts->getType()->value,
        );

        $createDtos = [];
        $indexes = [];
        foreach ($indexesData as $k => $indexesDatum) {
            [
                'subject' => $subject,
                'teacherEmail' => $teacherEmail,
                'studentEmail' => $studentEmail,
                'type' => $type,
                'actualFrom' => $actualFrom,
                'actualTo' => $actualTo,
            ] = $indexesDatum;

            $teacherSubject = $teacherSubjects[$subject][$teacherEmail->getEmail()][$type->value] ?? null;
            if ($teacherSubject === null) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'этот преподаватель не ведет этот предмет не найден',
                        ),
                    ),
                ]);
            }
            $student = $students[$studentEmail->getEmail()];

            $createDto = new CreateStudentSubjectDto(
                $student,
                $teacherSubject,
                $actualFrom,
                $actualTo,
            );

            try {
                $this
                    ->studentSubjectService
                    ->validateCreateDto($createDto);
            } catch (ValidationException $e) {
                throw ValidationException::new(
                    array_map(
                        fn(ValidationError $error) => new ValidationError(
                            'file',
                            ValidationErrorSlugEnum::WrongFile->getSlug(),
                            sprintf($validationErrorTemplate, $k, $error->getMessage()),
                        ),
                        $e->getErrors(),
                    ),
                );
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректные данные',
                        ),
                    ),
                ]);
            }

            $indexes[$k] = new GetStudentSubjectByIntersectionDto(
                $student->getId(),
                $teacherSubject->getId(),
                $actualFrom,
                $actualTo,
            );
            $createDtos[] = $createDto;
        }

        $existingStudentSubjects = $this
            ->studentSubjectService
            ->getAllByIntersections($indexes);
        if (iterator_count($existingStudentSubjects) > 0) {
            $student = $existingStudentSubjects->current()->getUser();
            $teacher = $existingStudentSubjects->current()->getTeacher();
            $subject = $existingStudentSubjects->current()->getSubject();
            $type = $existingStudentSubjects->current()->getType();
            $row = $existingRows[$student->getEmail()->getEmail()][$teacher->getEmail()->getEmail()][$subject->getName()][$type->value];
            throw ValidationException::new([
                new ValidationError(
                    'file',
                    ValidationErrorSlugEnum::WrongFile->getSlug(),
                    sprintf(
                        $validationErrorTemplate,
                        $row - 1 + $firstRow,
                        'этот студент уже ходит на такой предмет в этот промежуток времени',
                    ),
                ),
            ]);
        }

        $chunks = array_chunk($createDtos, 100, true);

        $this->transactionManager->beginTransaction();
        $created = 0;
        foreach ($chunks as $dtos) {
            try {
                $created += $this->studentSubjectService->createMulti($dtos, false, false, true);
            } catch (Throwable $e) {
                $this->logger->error($e);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить предметы для студентов, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
