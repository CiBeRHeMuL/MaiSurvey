<?php

namespace App\Domain\Service\Subject;

use App\Domain\Dto\Semester\GetSemesterByIndexDto;
use App\Domain\Dto\Subject\CreateSubjectDto;
use App\Domain\Dto\Subject\GetByRawIndexDto;
use App\Domain\Dto\Subject\ImportDto;
use App\Domain\Entity\Semester;
use App\Domain\Entity\Subject;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Semester\SemesterService;
use App\Domain\Validation\ValidationError;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class SubjectsImporter
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TransactionManagerInterface $transactionManager,
        private SubjectService $subjectService,
        private FileReaderInterface $dataImport,
        private SemesterService $semesterService,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): SubjectsImporter
    {
        $this->logger = $logger;
        $this->subjectService->setLogger($logger);
        $this->semesterService->setLogger($logger);
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

        /** @var array<string, CreateSubjectDto> $createDtos */
        $createDtos = [];
        // Мапа название -> номер строки. Для вывода ошибки
        $existingRows = [];
        $names = [];
        /** @var GetSemesterByIndexDto[] $semesterIndexes */
        $semesterIndexes = [];
        /** @var GetByRawIndexDto[] $indexes */
        $indexes = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $name = trim($row[$dto->getNameCol()] ?? '');
            $year = trim($row[$dto->getYearCol()] ?? '');
            $semesterNumber = trim($row[$dto->getSemesterCol()] ?? '');

            $hash = md5("$name$year$semesterNumber");
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

            if (!ctype_digit($year) && strlen($year) !== 4) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'год указан неверно. Укажите год 4-мя цифрами',
                    ),
                ]);
            }
            $year = (int)$year;

            if (!ctype_digit($semesterNumber) && !in_array($semesterNumber, ['1', '2'])) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        'семестр указан неверно. Укажите его одной цифрой (1 - весенний семестр, 2 - осенний семестр)',
                    ),
                ]);
            }
            $semesterNumber = (int)$semesterNumber;
            $isSpringSemester = (bool)($semesterNumber % 2);

            $existingRows[$hash] = $k;
            $names[$k] = $name;
            $semesterIndex = new GetSemesterByIndexDto(
                $year,
                $isSpringSemester,
            );
            $indexes[] = new GetByRawIndexDto(
                $name,
                $semesterIndex,
            );
            $semesterIndexes[] = $semesterIndex;
        }

        $semesters = $this
            ->semesterService
            ->getByIndexes($semesterIndexes);
        $semesters = HArray::index(
            $semesters,
            function (Semester $s) {
                $year = $s->getYear();
                $isSpring = (int)$s->isSpring();
                return md5("$year$isSpring");
            },
        );

        $existingSubjects = $this
            ->subjectService
            ->getByIndexes($indexes);
        $existingSubjects = HArray::index(
            $existingSubjects,
            function (Subject $s) {
                $name = $s->getName();
                $year = $s->getSemester()->getYear();
                $isSpring = (int)$s->getSemester()->isSpring();
                return md5("$name$year$isSpring");
            },
        );

        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $name = trim($row[$dto->getNameCol()] ?? '');
            $year = trim($row[$dto->getYearCol()] ?? '');
            $semesterNumber = ((int)trim($row[$dto->getSemesterCol()] ?? '') % 2);

            $semHash = md5("$year$semesterNumber");

            $semester = $semesters[$semHash] ?? null;
            if ($semester === null) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'семестр не найден',
                        ),
                    ),
                ]);
            }

            $hash = md5("$name$year$semesterNumber");
            if (isset($existingSubjects[$hash])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'такой предмет уже существует',
                        ),
                    ),
                ]);
            }

            $createDto = new CreateSubjectDto(
                $name,
                $semester,
            );

            try {
                $this->subjectService->validateCreateDto($createDto, false);
            } catch (ValidationException $e) {
                throw ValidationException::new(array_map(
                    fn(ValidationError $error) => new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf($validationErrorTemplate, $k - 1 + $firstRow, $error->getMessage()),
                    ),
                    $e->getErrors(),
                ));
            }

            $createDtos[] = $createDto;
        }

        // Сохраняем по 100 записей за раз, чтобы делать меньше запросов
        $chunks = array_chunk($createDtos, 100, true);

        $this->transactionManager->beginTransaction();
        $created = 0;
        foreach ($chunks as $dtos) {
            try {
                $created += $this->subjectService->createMulti($dtos, false, false, true);
            } catch (Throwable $e) {
                $this->logger->error($e);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить предметы, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
