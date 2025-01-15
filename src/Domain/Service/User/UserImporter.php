<?php

namespace App\Domain\Service\User;

use App\Domain\DataProvider\DataSort;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\DataProvider\SortColumn;
use App\Domain\Dto\User\CreateUserDto;
use App\Domain\Dto\User\ImportDto;
use App\Domain\Dto\UserData\ImportDto as UserDataImportDto;
use App\Domain\Entity\User;
use App\Domain\Entity\UserData;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HString;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\UserData\UserDataImporter;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\ValueObject\Email;
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

    public function import(ImportDto $dto): int
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
                return 0;
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

            $userDtos = new ProjectionAwareDataProvider(
                $allUserData,
                function (UserData $userData) use (&$dto, &$existingEmails, &$userDataByEmail): CreateUserDto {
                    $emailUser = HString::rusToEnd(
                        $userData->getLastName()
                        . mb_substr($userData->getFirstName(), 0, 1)
                        . mb_substr($userData->getPatronymic() ?? '', 0, 1),
                    );
                    $k = 1;
                    $emailDomain = '@mai-survey.net';
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

            $usersDataToUpdate = new ProjectionAwareDataProvider(
                $users,
                function (User $user) use (&$userDataByEmail): UserData {
                    $userData = $userDataByEmail[$user->getEmail()->getEmail()];
                    $userData
                        ->setUserId($user->getId())
                        ->setUser($user);
                    return $userData;
                },
            );

            $updatedUsersDataCount = $this->userDataService->updateMulti(
                iterator_to_array($usersDataToUpdate->getItems()),
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
            return $created;
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
