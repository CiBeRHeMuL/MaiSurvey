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
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Exception\ValidationException;
use App\Domain\Helper\HString;
use App\Domain\Service\Db\TransactionManagerInterface;
use App\Domain\Service\FileReader\FileReaderInterface;
use App\Domain\Service\Security\PasswordCheckerServiceInterface;
use App\Domain\Service\UserData\UserDataImporter;
use App\Domain\Service\UserData\UserDataService;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use ArrayIterator;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Iterator;
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
        private FileReaderInterface $fileReader,
        private PasswordCheckerServiceInterface $passwordCheckerService,
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
        $this->passwordCheckerService->checkPasswordStrength($dto->getPassword());

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
            /** @var Iterator<int, array<string, string>> $presetData */
            $presetData = $this->fileReader->getRows(
                $dto->isHeadersInFirstRow() ? 2 : 1,
                $this->fileReader->getHighestRow(),
            );
            $arrayPresetData = iterator_to_array($presetData);
            $presetData = new ArrayIterator($arrayPresetData);
            $userDataCount = $this->userDataImporter->importFromIterator($importUserDataDto, $presetData);

            if ($userDataCount === 0) {
                return new CreatedUsersInfo(0);
            }

            $validationErrorTemplate = 'Некорректное содержимое файла. Ошибка в строке %d: %s';

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
                function (UserData $userData) {
                    return $userData->getFullName();
                },
            );

            $existingEmails = array_unique(array_merge(
                $this
                    ->userDataService
                    ->getEmailsByNames(iterator_to_array($names->getItems())),
                $this
                    ->userService
                    ->getEmailsByEmails(array_filter(array_column(
                        $arrayPresetData,
                        $dto->getEmailCol(),
                    )))
            ));


            /** @var array<string, UserData> $userDataByEmail */
            $userDataByEmail = [];
            $emailDomain = '@' . $dto->getForRole()->value . '.' . $this->appHost;

            $presetData->rewind();
            $userDtos = new ProjectionAwareDataProvider(
                $allUserData,
                function (UserData $userData) use (
                    &$dto,
                    &$existingEmails,
                    &$userDataByEmail,
                    &$emailDomain,
                    &$presetData,
                    $validationErrorTemplate,
                ): CreateUserDto {
                    /** @var string|null $presetEmail */
                    $presetEmail = $presetData->current()[$dto->getEmailCol()] ?? null ?: null;
                    if ($presetEmail !== null) {
                        try {
                            $presetEmail = new Email($presetEmail);
                        } catch (Throwable) {
                            throw ValidationException::new([
                                new ValidationError(
                                    'file',
                                    ValidationErrorSlugEnum::WrongFile->getSlug(),
                                    sprintf(
                                        $validationErrorTemplate,
                                        $presetData->key(),
                                        'некорректный формат почты',
                                    ),
                                ),
                            ]);
                        }
                    }
                    $email = null;
                    if ($presetEmail === null) {
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
                        $email = new Email("$tempEmail$emailDomain");
                    } else {
                        /** @var Email $presetEmail */
                        if (in_array($presetEmail->getEmail(), $existingEmails)) {
                            throw ValidationException::new([
                                new ValidationError(
                                    'file',
                                    ValidationErrorSlugEnum::WrongFile->getSlug(),
                                    sprintf(
                                        $validationErrorTemplate,
                                        $presetData->key(),
                                       'пользователь с такой почтой уже существует',
                                    ),
                                ),
                            ]);
                        }
                        $email = $presetEmail;
                    }

                    $existingEmails[] = $email->getEmail();
                    $userDataByEmail[$email->getEmail()] = $userData;
                    $presetData->next();
                    return new CreateUserDto(
                        $email,
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
