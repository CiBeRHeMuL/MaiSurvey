<?php

namespace App\Domain\Service\User;

use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\User\CreatedUsersInfo;
use App\Domain\Dto\User\CreateUserDto;
use App\Domain\Dto\User\GetAllUsersDto;
use App\Domain\Dto\User\ImportDto;
use App\Domain\Dto\UserData\ImportDto as UserDataImportDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserData;
use App\Domain\Enum\SortTypeEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HString;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\UserData\UserDataImporter;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class UserImporter
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private UserDataImporter $userDataImporter,
        private TransactionManagerInterface $transactionManager,
        private UserService $userService,
        private UserDataService $userDataService,
        private string $appHost,
    ) {
        $this->setLogger($logger);
    }

    public function setLogger(LoggerInterface $logger): UserImporter
    {
        $this->logger = $logger;
        $this->userDataImporter->setLogger($logger);
        $this->userService->setLogger($logger);
        $this->userDataService->setLogger($logger);
        return $this;
    }

    public function import(ImportDto $dto): CreatedUsersInfo
    {
        $importUserDataDto = new UserDataImportDto(
            $dto->getFile(),
            $dto->getForRole(),
            $dto->isHeadersInFirstRow(),
            $dto->getLastNameCol(),
            $dto->getFirstNameCol(),
            $dto->getPatronymicCol(),
            $dto->getGroupNameCol(),
        );

        try {
            $this->transactionManager->beginTransaction();
            $userDataCount = $this->userDataImporter->import($importUserDataDto);

            if ($userDataCount === 0) {
                return new CreatedUsersInfo(0);
            }

            $allUserData = $this
                ->userDataService
                ->getLastN(
                    $userDataCount,
                    new DataSort([
                        new SortColumn(
                            'created_at',
                            'created_at',
                            SORT_DESC,
                        ),
                    ]),
                );

            $names = new ProjectionAwareDataProvider(
                $allUserData,
                function (UserData $userData) use ($dto) {
                    return $userData->getFullName();
                },
            );

            $existingEmails = $this
                ->userDataService
                ->getEmailsByNames(iterator_to_array($names->getItems()));

            /** @var array<string, UserData> $userDataByEmail */
            $userDataByEmail = [];
            $emailDomain = '@' . $dto->getForRole()->value . '.' . $this->appHost;

            $userDtos = new ProjectionAwareDataProvider(
                $allUserData,
                function (UserData $userData) use (&$dto, &$existingEmails, &$userDataByEmail, &$emailDomain): CreateUserDto {
                    $emailUser = HString::rusToEnd(
                        $userData->getLastName()
                        . mb_substr($userData->getFirstName(), 0, 1)
                        . mb_substr($userData->getPatronymic() ?? '', 0, 1),
                    );
                    $k = 1;
                    $tempEmail = $emailUser;
                    while (in_array("$tempEmail$emailDomain", $existingEmails)) {
                        $tempEmail = "$emailUser$k";
                        $k++;
                    }
                    $email = "$tempEmail$emailDomain";
                    $existingEmails[] = $email;
                    $userDataByEmail[$email] = $userData;
                    return new CreateUserDto(
                        new Email($email),
                        UserStatusEnum::Active,
                        $dto->getForRole(),
                        $dto->getPassword(),
                    );
                },
            );

            $created = $this->userService->createMulti(
                iterator_to_array($userDtos->getItems()),
                false,
                throwOnError: true,
            );

            if ($created !== $userDataCount) {
                throw new Exception(sprintf(
                    'Несовпадение количества сохраненных пользователей и данных: %d -> %d',
                    $created,
                    $userDataCount,
                ));
            }

            $users = $this->userService->getLastN(
                $created,
                new DataSort([
                    new SortColumn(
                        'created_at',
                        'created_at',
                        SORT_DESC,
                    ),
                ]),
            );

            $createdFrom = new DateTimeImmutable();
            $createdTo = $createdFrom->modify('-1 hour');
            // Подготавливаем данные для обновления, сразу считаем минимальное и максимальное время создани
            // (да, криво, но для лучшей производительности)
            $usersDataToUpdate = new ProjectionAwareDataProvider(
                $users,
                function (User $user) use (&$userDataByEmail, &$createdFrom, &$createdTo): UserData {
                    $createdTo = $user->getCreatedAt()->getTimestamp() > $createdTo->getTimestamp()
                        ? $user->getCreatedAt()
                        : $createdTo;
                    $createdFrom = $user->getCreatedAt()->getTimestamp() < $createdFrom->getTimestamp()
                        ? $user->getCreatedAt()
                        : $createdFrom;

                    $userData = $userDataByEmail[$user->getEmail()->getEmail()];
                    $userData
                        ->setUserId($user->getId())
                        ->setUser($user);
                    return $userData;
                },
            );

            $usersDataToUpdate = iterator_to_array($usersDataToUpdate->getItems());
            $updatedUsersDataCount = $this->userDataService->updateMulti(
                $usersDataToUpdate,
                false,
                true,
            );

            if ($created !== $updatedUsersDataCount) {
                throw new Exception(sprintf(
                    'Несовпадение количества сохраненных пользователей и обновленных данных: %d -> %d',
                    $created,
                    $updatedUsersDataCount,
                ));
            }

            $this->transactionManager->commit();

            $groupIds = null;
            if ($dto->getForRole()->requiresGroup()) {
                $groupIds = array_filter(
                    array_unique(
                        array_map(
                            fn(UserData $ud) => $ud->getGroup()?->getGroupId(),
                            $usersDataToUpdate,
                        ),
                    ),
                );
                if (count($groupIds) > 50) {
                    $groupIds = null;
                }
            }
            return new CreatedUsersInfo(
                $created,
                new GetAllUsersDto(
                    roles: [$dto->getForRole()],
                    email: $emailDomain,
                    deleted: false,
                    status: UserStatusEnum::Active,
                    groupIds: $groupIds,
                    withGroup: $dto->getForRole()->requiresGroup(),
                    createdFrom: $createdFrom,
                    createdTo: $createdTo,
                    sortBy: 'created_at',
                    sortType: SortTypeEnum::Desc,
                    limit: $created,
                ),
            );
        } catch (ValidationException|ErrorException $e) {
            $this->transactionManager->rollback();
            throw $e;
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            $this->logger->error($e);
            throw ErrorException::new(
                'Не удалось создать пользователей, обратитесь в поддержку',
            );
        }
    }
}
