<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Dto\StudentSubject\CreatedStudentSubjectsInfo;
use App\Domain\Dto\StudentSubject\CreateStudentSubjectDto;
use App\Domain\Dto\StudentSubject\GetSSByIndexRawDto;
use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Dto\TeacherSubject\GetTSByIndexRawDto;
use App\Domain\Entity\Semester;
use App\Domain\Entity\StudentSubject;
use App\Domain\Entity\TeacherSubject;
use App\Domain\Entity\User;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Repository\SemesterRepositoryInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Subject\SubjectService;
use App\Domain\Service\SurveyStat\StatRefresherInterface;
use App\Domain\Service\TeacherSubject\TeacherSubjectService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use ArrayIterator;
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
        private SemesterRepositoryInterface $semesterRepository,
        private StatRefresherInterface $statRefresher,
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
        $data = iterator_to_array($data);
        if (count($data) > 100) {
            $this->transactionManager->beginTransaction();
            $chunks = array_chunk($data, 100, true);
            $allCreated = 0;
            $allSkipped = 0;
            foreach ($chunks as $chunk) {
                try {
                    $createdInfo = $this->importFromIterator($dto, new ArrayIterator($chunk));
                    $allCreated += $createdInfo->getCreated();
                    $allSkipped += $createdInfo->getSkipped();
                } catch (ValidationException|ErrorException $e) {
                    $this->transactionManager->rollback();
                    throw $e;
                } catch (Throwable $e) {
                    $this->transactionManager->rollback();
                    throw ErrorException::new('Не удалось сохранить предметы для студентов, обратитесь в поддержку');
                }
            }
            $this->transactionManager->commit();
            return new CreatedStudentSubjectsInfo($allCreated, $allSkipped);
        }

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
        /** @var GetSSByIndexRawDto[] $studentSubjectIntersectionIndexes */
        $studentSubjectIntersectionIndexes = [];
        /** @var GetSemesterByIndexDto[] $semesterIndexes */
        $semesterIndexes = [];
        foreach ($data as $k => $row) {
            $studentEmail = mb_strtolower(trim($row[$dto->getStudentEmailCol()] ?? ''));
            $teacherEmail = mb_strtolower(trim($row[$dto->getTeacherEmailCol()] ?? ''));
            $subject = mb_strtolower(trim($row[$dto->getSubjectCol()] ?? ''));
            $type = trim($row[$dto->getTypeCol()] ?? '');
            $year = trim($row[$dto->getYearCol()] ?? '');
            $semesterNumber = trim($row[$dto->getSemesterCol()] ?? '');

            $hash = md5("$studentEmail$teacherEmail$subject$type$year$semesterNumber");
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
                        'некорректный тип предмета',
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

            $teacherSubjectIndex = new GetTSByIndexRawDto(
                $teacherEmail,
                $subject,
                $type,
                new GetSemesterByIndexDto(
                    $year,
                    $isSpringSemester,
                ),
            );
            $teacherSubjectIndexes[] = $teacherSubjectIndex;
            $studentSubjectIntersectionIndexes[] = new GetSSByIndexRawDto(
                $studentEmail,
                $teacherSubjectIndex,
                new GetSemesterByIndexDto(
                    $year,
                    $isSpringSemester,
                ),
            );
            $semesterIndexes[] = new GetSemesterByIndexDto(
                $year,
                $isSpringSemester,
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
            ->getAllByRawIndexes($studentSubjectIntersectionIndexes);
        $semesters = $this
            ->semesterRepository
            ->findAllByIndexes($semesterIndexes);

        /** @var array<string, User> $students */
        $students = HArray::index(
            $students,
            fn(User $s) => mb_strtolower($s->getEmail()->getEmail()),
        );
        /** @var array<string, TeacherSubject> $teacherSubjects */
        $teacherSubjects = HArray::index(
            $teacherSubjects,
            function (TeacherSubject $ts) {
                $teacherEmail = mb_strtolower($ts->getTeacher()->getEmail()->getEmail());
                $subject = mb_strtolower($ts->getSubject()->getName());
                $type = $ts->getType()->value;
                $semYear = $ts->getSubject()->getSemester()->getYear();
                $semSpring = (int)$ts->getSubject()->getSemester()->isSpring();
                return md5("$teacherEmail$subject$type$semYear$semSpring");
            },
        );
        /** @var array<string, StudentSubject> $studentSubjects */
        $studentSubjects = HArray::index(
            $studentSubjects,
            function (StudentSubject $ss) {
                $studentEmail = mb_strtolower($ss->getUser()->getEmail()->getEmail());
                $teacherEmail = mb_strtolower($ss->getTeacher()->getEmail()->getEmail());
                $subject = mb_strtolower($ss->getSubject()->getName());
                $type = $ss->getTeacherSubject()->getType()->value;
                $year = $ss->getSubject()->getSemester()->getYear();
                $semesterNumber = (int)$ss->getSubject()->getSemester()->isSpring();
                return md5("$studentEmail$teacherEmail$subject$type$year$semesterNumber");
            },
        );
        /** @var array<string, Semester> $semesters */
        $semesters = HArray::index(
            $semesters,
            fn(Semester $s) => md5("{$s->getYear()}" . (int)$s->isSpring()),
        );

        /** @var CreateStudentSubjectDto[] $createDtos */
        $createDtos = [];
        $skipped = 0;
        foreach ($data as $k => $row) {
            $studentEmail = mb_strtolower(trim($row[$dto->getStudentEmailCol()]));
            $teacherEmail = mb_strtolower(trim($row[$dto->getTeacherEmailCol()]));
            $subject = mb_strtolower(trim($row[$dto->getSubjectCol()]));
            $type = trim($row[$dto->getTypeCol()]);
            $year = (int)trim($row[$dto->getYearCol()]);
            $semesterNumber = (int)trim($row[$dto->getSemesterCol()]) % 2;

            $student = $students[$studentEmail] ?? null;
            if ($student === null || $student->isStudent() === false) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'студент не найден',
                    ),
                ]);
            } elseif (
                $student->getGroup() !== null
                && $dto->getOnlyForGroupId() !== null
                && $student->getGroup()->getId()->equals($dto->getOnlyForGroupId()) === false
            ) {
                $skipped++;
                continue;
            }

            $semHash = md5("$year$semesterNumber");
            $ssHash = md5("$studentEmail$teacherEmail$subject$type$year$semesterNumber");
            $tsHash = md5("$teacherEmail$subject$type$year$semesterNumber");

            $semester = $semesters[$semHash] ?? null;
            if ($semester === null) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'семестр не найден',
                    ),
                ]);
            }

            $existingSs = $studentSubjects[$ssHash] ?? null;
            if ($existingSs) {
                if ($dto->isSkipIfExists()) {
                    $skipped++;
                    continue;
                } else {
                    throw ValidationException::new([
                        $errorGenerator(
                            $k,
                            'студент уже ходит на этот предмет в указанном семестре',
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
                            'этот преподаватель не ведет предмет "%s" в указанном семестре',
                            mb_ucfirst($subject),
                        ),
                    ),
                ]);
            }

            $createDtos[] = new CreateStudentSubjectDto(
                $student,
                $ts,
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
        $this->statRefresher->refreshStats();
        return new CreatedStudentSubjectsInfo(
            $created,
            $skipped,
        );
    }
}
