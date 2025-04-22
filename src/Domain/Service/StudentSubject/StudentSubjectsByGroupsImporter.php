<?php

namespace App\Domain\Service\StudentSubject;

use App\Domain\Dto\StudentSubject\CreatedStudentSubjectsInfo;
use App\Domain\Dto\StudentSubject\ImportByGroupsDto;
use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Group\GroupService;
use App\Domain\Service\User\UserService;
use App\Domain\Validation\ValidationError;
use ArrayIterator;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class StudentSubjectsByGroupsImporter
{
    private LoggerInterface $logger;

    public function __construct(
        private FileReaderInterface $fileReader,
        private GroupService $groupService,
        private StudentSubjectsImporter $studentSubjectsImporter,
        private UserService $userService,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): StudentSubjectsByGroupsImporter
    {
        $this->logger = $logger;
        $this->groupService->setLogger($logger);
        $this->studentSubjectsImporter->setLogger($logger);
        $this->userService->setLogger($logger);
        return $this;
    }

    public function import(ImportByGroupsDto $dto): CreatedStudentSubjectsInfo
    {
        try {
            $this->fileReader->openFile($dto->getFile());
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

        $firstRow = $dto->isHeadersInFirstRow() ? 2 : 1;

        /** @var string[] $groupNames */
        $groupNames = [];
        /** @var array<string, int> $firstGroupRows */
        $firstGroupRows = [];
        foreach ($this->fileReader->getRows($firstRow, $this->fileReader->getHighestRow()) as $k => $row) {
            $groupName = $row[$dto->getGroupNameCol()] ?? '';
            $groupNames[] = $groupName;
            $firstGroupRows[$groupName] ??= $k;
        }

        $groups = [];
        if ($dto->getOnlyForGroupId()) {
            $group = $this->groupService->getById($dto->getOnlyForGroupId());
            $group && $groups[] = $group;
        } else {
            $groups = $this
                ->groupService
                ->getByNames($groupNames);
        }

        /** @var array<string, Group> $groups */
        $groups = HArray::index(
            $groups,
            fn(Group $g) => $g->getName(),
        );

        foreach ($firstGroupRows as $groupName => $k) {
            if (!isset($groups[$groupName])) {
                throw ValidationException::new([
                    $errorGenerator(
                        $k,
                        sprintf('группа "%s" не найдена', $groupName),
                    ),
                ]);
            }
        }

        /** @var array<string, User[]> $students */
        $students = HArray::group(
            $this
                ->userService
                ->getAll(
                    new GetAllUsersDto(
                        roles: [RoleEnum::Student],
                        groupIds: array_map(fn(Group $g) => $g->getId(), $groups),
                    ),
                )
                ->getItems(),
            fn(User $u) => $u->getData()->getGroup()->getGroup()->getName(),
        );

        $rowsMap = [];
        $data = [];
        $i = 0;
        foreach ($this->fileReader->getRows($firstRow, $this->fileReader->getHighestRow()) as $k => $row) {
            $groupName = $row[$dto->getGroupNameCol()];
            foreach ($students[$groupName] ?? [] as $student) {
                $data[$i] = [
                    $dto->getGroupNameCol() => $student->getEmail()->getEmail(),
                    $dto->getTeacherEmailCol() => $row[$dto->getTeacherEmailCol()] ?? '',
                    $dto->getSubjectCol() => $row[$dto->getSubjectCol()] ?? '',
                    $dto->getTypeCol() => $row[$dto->getTypeCol()] ?? '',
                    $dto->getYearCol() => $row[$dto->getYearCol()] ?? '',
                    $dto->getSemesterCol() => $row[$dto->getSemesterCol()] ?? '',
                ];
                $i++;
                $rowsMap[$i] = $k;
            }
        }

        unset($students);
        unset($groups);

        try {
            return $this
                ->studentSubjectsImporter
                ->importFromIterator(
                    new ImportDto(
                        $dto->getFile(),
                        false,
                        $dto->getGroupNameCol(),
                        $dto->getTeacherEmailCol(),
                        $dto->getSubjectCol(),
                        $dto->getTypeCol(),
                        $dto->getYearCol(),
                        $dto->getSemesterCol(),
                        $dto->isSkipIfExists(),
                        $dto->getOnlyForGroupId(),
                    ),
                    new ArrayIterator($data),
                );
        } catch (ValidationException $e) {
            throw ValidationException::new(array_map(
                fn(ValidationError $er) => new ValidationError(
                    $er->getField(),
                    $er->getSlug(),
                    preg_replace_callback(
                        '/(?<=(Ошибка в строке|такой набор уже был указан в строке) )(\d+)/ui',
                        fn($m) => $rowsMap[$m[0]] ?? $m[0],
                        $er->getMessage(),
                    ),
                ),
                $e->getErrors(),
            ));
        }
    }
}
