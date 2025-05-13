<?php

namespace App\Domain\Service\UserData;

use App\Domain\Dto\UserData\CreateUserDataDto;
use App\Domain\Dto\UserData\ImportDto;
use App\Domain\Entity\Group;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Group\GroupService;
use App\Domain\Validation\ValidationError;
use InvalidArgumentException;
use Iterator;
use Psr\Log\LoggerInterface;
use Throwable;

class UserDataImporter
{
    private LoggerInterface $logger;

    public function __construct(
        private FileReaderInterface $dataImport,
        private UserDataService $userDataService,
        private GroupService $groupService,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
    }

    public function setLogger(LoggerInterface $logger): UserDataImporter
    {
        $this->logger = $logger;
        $this->userDataService->setLogger($logger);
        $this->groupService->setLogger($logger);
        return $this;
    }

    public function import(ImportDto $dto): int
    {
        if ($dto->getForRole()->importEnabled() === false) {
            throw ValidationException::new([
                new ValidationError(
                    'for_role',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Импорт данных недоступен для этой роли',
                ),
            ]);
        }

        try {
            $this->dataImport->openFile($dto->getFile());
        } catch (InvalidArgumentException $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            throw ValidationException::new([
                new ValidationError(
                    'file',
                    ValidationErrorSlugEnum::FileNotExists->getSlug(),
                    'Не удалось открыть файл',
                ),
            ]);
        }
        $firstRow = $dto->isHeadersInFirstRow() ? 2 : 1;

        $ids = $this->importFromIteratorReturningIds($dto, $this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()));
        return count($ids);
    }

    /**
     * @param ImportDto $dto
     * @param Iterator $data
     *
     * @return string[]
     */
    public function importFromIteratorReturningIds(ImportDto $dto, Iterator $data): array
    {
        $data = iterator_to_array($data);
        // Сначала собираем id групп для
        /** @var string[] $groupNames */
        $groupNames = [];
        foreach ($data as $row) {
            $groupName = $row[$dto->getGroupNameCol()] ?? null;
            $groupName = trim($groupName);

            $groupName && $groupNames[$groupName] = $groupName;
        }

        $groups = $this
            ->groupService
            ->getByNames(array_values($groupNames));
        $groups = HArray::index(
            $groups,
            fn(Group $g) => $g->getName(),
        );

        $validationErrorTemplate = 'Некорректное содержимое файла. Ошибка в строке %d: %s';

        $createDtos = [];
        // Обрабатываем строки на ошибки
        foreach ($data as $k => $row) {
            $groupName = $row[$dto->getGroupNameCol()] ?? null;
            $groupName = trim($groupName);

            $firstName = $row[$dto->getFirstNameCol()] ?? '';
            $lastName = $row[$dto->getLastNameCol()] ?? '';
            $patronymic = $row[$dto->getPatronymicCol()] ?? null;
            $firstName = trim($firstName);
            $lastName = trim($lastName);
            $patronymic = $patronymic !== null ? trim($patronymic) : null;

            if ($groupName && !isset($groups[$groupName])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k,
                            sprintf(
                                'Группа "%s" не найдена',
                                $groupName,
                            ),
                        ),
                    ),
                ]);
            }

            $createDto = new CreateUserDataDto(
                $dto->getForRole(),
                $firstName,
                $lastName,
                $patronymic ?: null,
                $groups[$groupName] ?? null,
            );
            try {
                $this->userDataService->validateCreateDto($createDto);
            } catch (ValidationException $e) {
                throw ValidationException::new(array_map(
                    fn(ValidationError $error) => new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf($validationErrorTemplate, $k, $error->getMessage()),
                    ),
                    $e->getErrors(),
                ));
            }

            $createDtos[] = $createDto;
        }

        // Сохраняем по 100 записей за раз, чтобы делать меньше запросов
        $chunks = array_chunk($createDtos, 100, true);

        $this->transactionManager->beginTransaction();
        $created = [];
        foreach ($chunks as $dtos) {
            try {
                $created = array_merge(
                    $created,
                    $this->userDataService->createMultiReturningIds($dtos, false, false, true),
                );
            } catch (Throwable $e) {
                if (!$e instanceof ValidationError && !$e instanceof ErrorException) {
                    $this->logger->error('An error occurred', ['exception' => $e]);
                }
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить данные, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
