<?php

namespace App\Domain\Service\User;

use App\Domain\Dto\User\MultiUpdateDto;
use App\Domain\Dto\UserData\UpdateUserDataDto;
use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Entity\UserData;
use App\Domain\Entity\UserDataGroup;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HArray;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Group\GroupService;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\Service\UserDataGroup\UserDataGroupService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

class UserMultiUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private FileReaderInterface $dataImport,
        private UserService $userService,
        private GroupService $groupService,
        private UserDataService $userDataService,
        private UserDataGroupService $userDataGroupService,
        private TransactionManagerInterface $transactionManager,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserMultiUpdater
    {
        $this->logger = $logger;
        $this->userService->setLogger($logger);
        $this->groupService->setLogger($logger);
        $this->userDataGroupService->setLogger($logger);
        return $this;
    }

    public function multiUpdate(MultiUpdateDto $dto): int
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

        /** @var array<int, Email> $emails */
        $emails = [];
        $groupNames = [];
        // Мапа почта -> номер строки. Для вывода ошибки
        $emailToRow = [];

        // Мапа почта -> данные для обновления
        /** @var array<string, array{firstName: string, lastName: string, patronymic: string|null, group: string|null}> $updateDtosData */
        $updateDtosData = [];
        foreach ($this->dataImport->getRows($firstRow, $this->dataImport->getHighestRow()) as $k => $row) {
            $email = $row[$dto->getEmailCol()] ?? '';
            if (isset($emailToRow[$email])) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            sprintf(
                                'повторяющаяся почта, такой пользователь уже был указан в строке %d',
                                $emailToRow[$email] - 1 + $firstRow,
                            ),
                        ),
                    ),
                ]);
            }

            $emailToRow[$email] = $k;
            try {
                $email = new Email($email);
            } catch (Throwable) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'некорректное значение почты',
                        ),
                    ),
                ]);
            }
            $emails[$k] = $email;

            $group = $row[$dto->getGroupNameCol()] ?? null;
            $groupNames[$group] ??= $group;

            $updateDtosData[$email->getEmail()] = [
                'firstName' => $row[$dto->getFirstNameCol()] ?? '',
                'lastName' => $row[$dto->getLastNameCol()] ?? '',
                'patronymic' => $row[$dto->getPatronymicCol()] ?? null ?: null,
                'groupName' => $row[$dto->getGroupNameCol()] ?? null ?: null,
            ];
        }

        if (!$emails) {
            return 0;
        }

        $groups = $this
            ->groupService
            ->getByNames($groupNames);
        /** @var array<string, Group> $groups */
        $groups = HArray::index(
            $groups,
            fn(Group $g) => $g->getName(),
        );

        $users = $this
            ->userService
            ->getAllByEmails($emails);

        /** @var array<string, User> $users */
        $users = HArray::index(
            $users,
            fn(User $u): string => $u->getEmail()->getEmail(),
        );

        /** @var UserData[] $userDataToUpdate */
        $userDataToUpdate = [];
        /** @var UserDataGroup[] $userDataGroupsToUpdate */
        $userDataGroupsToUpdate = [];
        foreach ($emails as $k => $email) {
            $user = $users[$email->getEmail()] ?? null;
            if ($user === null) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'пользователь с такой почтой не найден',
                        ),
                    ),
                ]);
            } elseif ($user->isActive() === false) {
                throw ValidationException::new([
                    new ValidationError(
                        'file',
                        ValidationErrorSlugEnum::WrongFile->getSlug(),
                        sprintf(
                            $validationErrorTemplate,
                            $k - 1 + $firstRow,
                            'нельзя обновить неактивного пользователя',
                        ),
                    ),
                ]);
            }

            $updateData = $updateDtosData[$email->getEmail()];
            $group = null;
            if ($updateData['groupName'] !== null) {
                $group = $groups[$updateData['groupName']] ?? null;
                if ($updateData['groupName'] && $group === null) {
                    throw ValidationException::new([
                        new ValidationError(
                            'file',
                            ValidationErrorSlugEnum::WrongFile->getSlug(),
                            sprintf(
                                $validationErrorTemplate,
                                $k - 1 + $firstRow,
                                'группа не найдена',
                            ),
                        ),
                    ]);
                }
            }

            $dto = new UpdateUserDataDto(
                $updateData['firstName'],
                $updateData['lastName'],
                $updateData['patronymic'],
                $group,
            );

            try {
                $this->userDataService->validateUpdateDto($user->getData(), $dto);
            } catch (ValidationException $e) {
                throw ValidationException::new(
                    array_map(
                        fn(ValidationError $error) => new ValidationError(
                            'file',
                            ValidationErrorSlugEnum::WrongFile->getSlug(),
                            sprintf($validationErrorTemplate, $k - 1 + $firstRow, $error->getMessage()),
                        ),
                        $e->getErrors(),
                    ),
                );
            }

            $userData = $user->getData();
            $userDataGroup = $user->getData()->getGroup();

            $userData
                ->setFirstName($dto->getFirstName())
                ->setLastName($dto->getLastName())
                ->setPatronymic($dto->getPatronymic())
                ->setUpdatedAt(new DateTimeImmutable());
            $userDataToUpdate[] = $userData;

            if ($userDataGroup) {
                $userDataGroup
                    ->setGroupId($group->getId())
                    ->setGroup($group)
                    ->setUpdatedAt(new DateTimeImmutable());
                $userDataGroupsToUpdate[] = $userDataGroup;
            }
        }

        $this->transactionManager->beginTransaction();
        try {
            $updated = $this
                ->userDataService
                ->updateMulti($userDataToUpdate, false, true);
            $this
                ->userDataGroupService
                ->updateMulti($userDataGroupsToUpdate, false, true);
            $this->transactionManager->commit();
            return $updated;
        } catch (Throwable $e) {
            $this->logger->error('An error occurred', ['exception' => $e]);
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
