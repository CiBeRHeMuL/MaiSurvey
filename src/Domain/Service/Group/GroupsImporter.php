<?php

namespace App\Domain\Service\Group;

use App\Domain\Dto\Group\CreateGroupDto;
use App\Domain\Dto\Group\ImportDto;
use App\Domain\Entity\Group;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Validation\ValidationError;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class GroupsImporter
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private TransactionManagerInterface $transactionManager,
        private GroupService $groupService,
        private FileReaderInterface $dataImport,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): GroupsImporter
    {
        $this->logger = $logger;
        $this->groupService->setLogger($logger);
        return $this;
    }

    public function import(ImportDto $dto): int
    {
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

        $validationErrorTemplate = 'Некорректное содержимое файла. Ошибка в строке %d: %s';

        /** @var array<string, CreateGroupDto> $createDtos */
        $createDtos = [];
        // Мапа название -> номер строки. Для вывода ошибки
        $nameToRow = [];
        // Названия групп
        $names = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $name = $row[$dto->getNameCol()] ?? '';
            $name = mb_strtolower(trim($name));
            if (isset($nameToRow[$name])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            sprintf(
                                'повторяющееся название группы, такая группа уже был указан в строке %d',
                                $nameToRow[$name] - 1 + $firstRow,
                            ),
                        ),
                    ),
                ]);
            }
            $nameToRow[$name] = $k;
            $names[$k] = $name;
        }

        $existingGroups = $this
            ->groupService
            ->getByNames($names);
        $existingGroups = HArray::index(
            $existingGroups,
            fn(Group $g) => mb_strtolower($g->getName()),
        );

        foreach ($names as $k => $name) {
            if (isset($existingGroups[$name])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'такая группа уже существует',
                        ),
                    ),
                ]);
            }

            $createDto = new CreateGroupDto(
                $name,
            );

            try {
                $this->groupService->validateCreateDto($createDto, false);
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
                $created += $this->groupService->createMulti($dtos, false, false, true);
            } catch (Throwable $e) {
                if (!$e instanceof ValidationError && !$e instanceof ErrorException) {
                    $this->logger->error('An error occurred', ['exception' => $e]);
                }
                $this->transactionManager->rollback();
                throw ErrorException::new('Не удалось сохранить группы, обратитесь в поддержку');
            }
        }
        $this->transactionManager->commit();
        return $created;
    }
}
