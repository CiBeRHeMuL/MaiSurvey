<?php

namespace App\Domain\Service\TeacherSubject;

use App\Domain\Dto\TeacherSubject\CreateTeacherSubjectDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexDto;
use App\Domain\Dto\TeacherSubject\ImportDto;
use App\Domain\Entity\Subject;
use App\Domain\Entity\User;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\DataImport\DataImportInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class TeacherSubjectsImporter
{
    private LoggerInterface $logger;

    public function __construct(
        private TeacherSubjectService $teacherSubjectService,
        private TransactionManagerInterface $transactionManager,
        private DataImportInterface $dataImport,
        private SubjectService $subjectService,
        private UserService $userService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): TeacherSubjectsImporter
    {
        $this->logger = $logger;
        $this->teacherSubjectService->setLogger($logger);
        $this->subjectService->setLogger($logger);
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

        /** @var array $dtos */
        $indexesData = [];
        $emails = [];
        $subjectNames = [];
        $types = [];
        /** @var array<string, array<string, array<string, true>>> $existingRows */
        $existingRows = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $subject = $row[$dto->getSubjectCol()] ?? '';
            $email = $row[$dto->getEmailCol()] ?? '';
            $type = $row[$dto->getTypeCol()] ?? '';

            if (isset($existingRows[$subject][$email][$type])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            sprintf(
                                'повторяющийся набор данных, такой набор уже был указан в строке %d',
                                $existingRows[$subject][$email][$type],
                            ),
                        ),
                    ),
                ]);
            }

            $existingRows[$subject][$email][$type] = $k;

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
                $email = new Email($email);
                $emails[$k] = $email;
            } catch (Throwable $e) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            'некорректная почта',
                        ),
                    ),
                ]);
            }
            $indexesData[$k] = compact('subject', 'email', 'type');
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
            ->getAllByEmails($emails);
        /** @var array<string, User> $teachers */
        $teachers = HArray::index(
            $teachers,
            fn(User $t) => $t->getEmail()->getEmail(),
        );

        $createDtos = [];
        $indexes = [];
        foreach ($indexesData as $k => $indexesDatum) {
            ['subject' => $subject, 'email' => $email, 'type' => $type] = $indexesDatum;
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
            $teacher = $teachers[$email->getEmail()] ?? null;
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

            $indexes[] = new GetTSByIndexDto(
                $teacher->getId(),
                $subject->getId(),
                $type,
            );
            $createDto = new CreateTeacherSubjectDto(
                $teacher,
                $subject,
                $type,
            );

            try {
                $this
                    ->teacherSubjectService
                    ->validateCreateDto($createDto);
            } catch (ValidationException $e) {
                throw ValidationException::new(array_map(
                    fn(ValidationError $error) => new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf($validationErrorTemplate, $k, $error->getMessage()),
                    ),
                    $e->getErrors(),
                ));
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
            $createDtos[] = $createDto;
        }

        $existingTeacherSubjects = $this
            ->teacherSubjectService
            ->getAllByIndexes($indexes);
        if (iterator_count($existingTeacherSubjects) > 0) {
            $teacher = $existingTeacherSubjects->current()->getTeacher();
            $subject = $existingTeacherSubjects->current()->getSubject();
            $type = $existingTeacherSubjects->current()->getType();
            $row = $existingRows[$subject->getName()][$teacher->getEmail()->getEmail()][$type->value];
            throw ValidationException::new([
                new ValidationError(
                    'file',
                    ValidationErrorSlugEnum::WrongFile->getSlug(),
                    sprintf(
                        $validationErrorTemplate,
                        $row - 1 + $firstRow,
                        'этот преподаватель уже ведет такой предмет',
                    ),
                ),
            ]);
        }

        $chunks = array_chunk($createDtos, 100, true);

        $this->transactionManager->beginTransaction();
        $created = 0;
        foreach ($chunks as $dtos) {
            try {
                $created += $this->teacherSubjectService->createMulti($dtos, false, false, true);
            } catch (Throwable $e) {
                $this->logger->error($e);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить предметы для преподавателей, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
