<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\Dto\StudentSubject\CreatedStudentSubjectsInfo;
use App\Domain\Dto\StudentSubject\CreateStudentSubjectDto;
use App\Domain\Dto\StudentSubject\GetSSByIntersectionRawDto;
use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\TeacherSubject\TeacherSubjectService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use InvalidArgumentException;
use Iterator;
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
        private FileReaderInterface $dataImport,
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

    public function import(ImportDto $dto): CreatedStudentSubjectsInfo
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

        return $this->importFromIterator(
            $dto,
            $this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()),
        );
    }

    public function importFromIterator(ImportDto $dto, Iterator $data): CreatedStudentSubjectsInfo
    {
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

        /** @var Email[] $studentEmails */
        $studentEmails = [];
        /** @var array<string, int> $firstStudentRow */
        $firstStudentRow = [];
        /** @var array<string, int> $existingRows */
        $existingRows = [];
        /** @var GetTSByIndexRawDto[] $teacherSubjectIndexes */
        $teacherSubjectIndexes = [];
        /** @var GetSSByIntersectionRawDto[] $studentSubjectIntersectionIndexes */
        $studentSubjectIntersectionIndexes = [];
        foreach ($data as $k => $row) {
            $studentEmail = $row[$dto->getStudentEmailCol()] ?? '';
            $teacherEmail = $row[$dto->getTeacherEmailCol()] ?? '';
            $subject = $row[$dto->getSubjectCol()] ?? '';
            $type = $row[$dto->getTypeCol()] ?? '';
            $actualFrom = $row[$dto->getActualFromCol()] ?? '';
            $actualTo = $row[$dto->getActualToCol()] ?? '';
            $studentEmail = trim($studentEmail);
            $teacherEmail = trim($teacherEmail);
            $subject = trim($subject);
            $type = trim($type);
            $actualFrom = trim($actualFrom);
            $actualTo = trim($actualTo);

            $hash = md5("$studentEmail$teacherEmail$subject$type");
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

            try {
                $studentEmail = new Email($studentEmail);
                $studentEmails[] = $studentEmail;
                $firstStudentRow[$studentEmail->getEmail()] ??= $k;
            } catch (Throwable $e) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат почты студента',
                    ),
                ]);
            }

            try {
                $teacherEmail = new Email($teacherEmail);
            } catch (Throwable $e) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат почты преподавателя',
                    ),
                ]);
            }

            $type = TeacherSubjectTypeEnum::tryFrom($type);
            if ($type === null) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат почты преподавателя',
                    ),
                ]);
            }

            try {
                $actualFrom = new DateTimeImmutable($actualFrom);
            } catch (Throwable $e) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат даты начала актуальности',
                    ),
                ]);
            }

            try {
                $actualTo = new DateTimeImmutable($actualTo);
            } catch (Throwable $e) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'некорректный формат даты конца актуальности',
                    ),
                ]);
            }

            if ($actualTo->getTimestamp() <= $actualFrom->getTimestamp()) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'дата окончания актуальности предмета должна быть больше даты начала актуальности',
                    ),
                ]);
            }

            $teacherSubjectIndex = new GetTSByIndexRawDto(
                $teacherEmail,
                $subject,
                $type,
            );
            $teacherSubjectIndexes[] = $teacherSubjectIndex;
            $studentSubjectIntersectionIndexes[] = new GetSSByIntersectionRawDto(
                $studentEmail,
                $teacherSubjectIndex,
                $actualFrom,
                $actualTo,
            );
        }

        $students = $this
            ->userService
            ->getAllByEmails($studentEmails);
        $teacherSubjects = $this
            ->teacherSubjectService
            ->getAllByRawIndexes($teacherSubjectIndexes);
        $studentSubjects = $this
            ->studentSubjectService
            ->getAllByRawIntersections($studentSubjectIntersectionIndexes);

        /** @var array<string, User> $students */
        $students = HArray::index(
            $students,
            fn(User $s) => $s->getEmail()->getEmail(),
        );
        /** @var array<string, TeacherSubject> $teacherSubjects */
        $teacherSubjects = HArray::index(
            $teacherSubjects,
            function (TeacherSubject $ts) {
                $teacherEmail = $ts->getTeacher()->getEmail()->getEmail();
                $subject = $ts->getSubject()->getName();
                $type = $ts->getType()->value;
                return md5("$teacherEmail$subject$type");
            },
        );
        /** @var array<string, StudentSubject> $studentSubjects */
        $studentSubjects = HArray::index(
            $studentSubjects,
            function (StudentSubject $ss) {
                $studentEmail = $ss->getUser()->getEmail()->getEmail();
                $teacherEmail = $ss->getTeacher()->getEmail()->getEmail();
                $subject = $ss->getSubject()->getName();
                $type = $ss->getTeacherSubject()->getType()->value;
                return md5("$studentEmail$teacherEmail$subject$type");
            },
        );

        /** @var CreateStudentSubjectDto[] $createDtos */
        $createDtos = [];
        $skipped = 0;
        foreach ($data as $k => $row) {
            $studentEmail = $row[$dto->getStudentEmailCol()];
            $teacherEmail = $row[$dto->getTeacherEmailCol()];
            $subject = $row[$dto->getSubjectCol()];
            $type = $row[$dto->getTypeCol()];
            $actualFrom = $row[$dto->getActualFromCol()];
            $actualTo = $row[$dto->getActualToCol()];

            $student = $students[$studentEmail] ?? null;
            if ($student === null || $student->isStudent() === false) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'студент не найден',
                    ),
                ]);
            }

            $ssHash = md5("$studentEmail$teacherEmail$subject$type");
            $tsHash = md5("$teacherEmail$subject$type");

            $existingSs = $studentSubjects[$ssHash] ?? null;
            if ($existingSs) {
                if ($dto->isSkipIfExists()) {
                    $skipped++;
                    continue;
                } else {
                    throw ValidationException::new([
                        $errorGenerator(
                            $k,
                            sprintf(
                                'студент уже ходит на этот предмет с %s по %s',
                                $existingSs->getActualFrom()->format('Y-m-d'),
                                $existingSs->getActualTo()->format('Y-m-d'),
                            ),
                        ),
                    ]);
                }
            }

            $ts = $teacherSubjects[$tsHash] ?? null;
            if ($ts === null) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        sprintf(
                            'этот преподаватель не ведет предмет "%s"',
                            $subject,
                        ),
                    ),
                ]);
            }

            $createDtos[] = new CreateStudentSubjectDto(
                $student,
                $ts,
                new DateTimeImmutable($actualFrom),
                new DateTimeImmutable($actualTo),
            );
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
        return new CreatedStudentSubjectsInfo(
            $created,
            $skipped,
        );
    }
}
