<?php

namespace App\Domain\Service\UserData;

use App\Domain\Dto\UserData\CreateUserDataDto;
use App\Domain\Dto\UserData\ImportDto;
use App\Domain\Entity\Group;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\DataImport\DataImportInterface;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\Group\GroupService;
use App\Domain\Validation\ValidationError;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class UserDataImporter
{
    private LoggerInterface $logger;

    public function __construct(
        private DataImportInterface $dataImport,
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
        if ($dto->getForRole()->importEnable() === false) {
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

        // Сначала собираем id групп для
        /** @var string[] $groupNames */
        $groupNames = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $row) {
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
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $groupName = $row[$dto->getGroupNameCol()] ?? null;
            $groupName = trim($groupName);

            $firstName = $row[$dto->getFirstNameCol()] ?? '';
            $lastName = $row[$dto->getLastNameCol()] ?? '';
            $patronymic = $row[$dto->getPatronymicCol()] ?? null;

            if ($groupName && !isset($groups[$groupName])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k + 1,
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
                        sprintf($validationErrorTemplate, $k + 1, $error->getMessage()),
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
                $created += $this->userDataService->createMulti($dtos, false, false, true);
            } catch (Throwable $e) {
                $this->logger->error($e);
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить данные, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
