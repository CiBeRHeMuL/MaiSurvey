<?php

namespace App\Domain\Service\TeacherSubject;

use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Dto\Subject\GetByRawIndexDto;
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
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
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
        private FileReaderInterface $dataImport,
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

        $errorGenerator = function (int $k, string $error) use (&$validationErrorTemplate): ValidationError {
            return new ValidationError(
                'file',
                ValidationErrorSlugEnum::WrongFile->getSlug(),
                sprintf(
                    $validationErrorTemplate,
                    $k,
                    $error,
                ),
            );
        };

        /** @var array $dtos */
        $indexesData = [];
        $emails = [];
        $subjectIndexes = [];
        $types = [];
        /** @var array<string, int> $existingRows */
        $existingRows = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $subject = mb_strtolower(trim($row[$dto->getSubjectCol()] ?? ''));
            $email = mb_strtolower(trim($row[$dto->getEmailCol()] ?? ''));
            $type = trim($row[$dto->getTypeCol()] ?? '');
            $year = trim($row[$dto->getYearCol()] ?? '');
            $semesterNumber = trim($row[$dto->getSemesterCol()] ?? '');

            $hash = md5("{$subject}_{$email}_{$type}_{$year}_{$semesterNumber}");
            if (isset($existingRows[$hash])) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        sprintf(
                            'повторяющийся набор данных, такой набор уже был указан в строке %d',
                            $existingRows[$hash],
                        ),
                    ),
                ]);
            }

            $existingRows[$hash] = $k;

            $type = TeacherSubjectTypeEnum::tryFrom($type);
            if ($type === null) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный тип предмета',
                    ),
                ]);
            }
            $types[$k] = $type;

            try {
                $email = new Email($email);
                $emails[$k] = $email;
            } catch (Throwable $e) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат почты преподавателя',
                    ),
                ]);
            }

            if (!ctype_digit($year) && strlen($year) !== 4) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'год указан неверно. Укажите год 4-мя цифрами',
                    ),
                ]);
            }
            $year = (int)$year;

            if (!ctype_digit($semesterNumber) || !in_array($semesterNumber, ['1', '2'])) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'семестр указан неверно. Укажите его одной цифрой (1 - весенний семестр, 2 - осенний семестр)',
                    ),
                ]);
            }
            $semesterNumber = (int)$semesterNumber;
            $isSpringSemester = (bool)($semesterNumber % 2);

            $indexesData[$k] = compact('subject', 'email', 'type', 'year', 'isSpringSemester');
            $subjectIndexes[$k] = new GetByRawIndexDto(
                $subject,
                new GetSemesterByIndexDto($year, $isSpringSemester),
            );
        }

        $subjects = $this
            ->subjectService
            ->getByRawIndexes($subjectIndexes);
        /** @var array<string, Subject> $subjects */
        $subjects = HArray::index(
            $subjects,
            function (Subject $s) {
                $name = mb_strtolower($s->getName());
                return md5("{$name}_{$s->getSemester()->getYear()}_{$s->getSemester()->isSpring()}");
            },
        );

        $teachers = $this
            ->userService
            ->getAllByEmails($emails);
        /** @var array<string, User> $teachers */
        $teachers = HArray::index(
            $teachers,
            fn(User $t) => mb_strtolower($t->getEmail()->getEmail()),
        );

        $createDtos = [];
        $indexes = [];
        foreach ($indexesData as $k => $indexesDatum) {
            ['subject' => $subject, 'email' => $email, 'type' => $type, 'year' => $year, 'isSpringSemester' => $isSpringSemester] = $indexesDatum;
            $subject = $subjects[md5("{$subject}_{$year}_{$isSpringSemester}")] ?? null;
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
            $teacher = $teachers[mb_strtolower($email->getEmail())] ?? null;
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
                    ->validateCreateDto($createDto, false);
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
                throw $e;
            }
            $createDtos[$k] = $createDto;
        }

        $existingTeacherSubjects = $this
            ->teacherSubjectService
            ->getAllByIndexes($indexes);
        foreach ($existingTeacherSubjects as $existingTeacherSubject) {
            $teacher = $existingTeacherSubject->getTeacher();
            $subject = $existingTeacherSubject->getSubject();
            $type = $existingTeacherSubject->getType();
            $semester = $subject->getSemester();
            $semesterNumber = (int)$semester->isSpring() ? 1 : 2;
            $sName = mb_strtolower($subject->getName());
            $tEmail = mb_strtolower($teacher->getEmail()->getEmail());
            $hash = md5("{$sName}_{$tEmail}_{$type->value}_{$semester->getYear()}_{$semesterNumber}");
            $row = $existingRows[$hash];
            if ($dto->isSkipIfExists()) {
                unset($createDtos[$row]);
            } else {
                throw ValidationException::new([
                    $errorGenerator(
                        $row,
                        'преподаватель уже ведет этот предмет в указанном семестре',
                    ),
                ]);
            }
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
