<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\Dto\User\GetAllUsersDto;
use App\Application\Dto\User\UpdateUserDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\DeleteUserUseCase;
use App\Application\UseCase\User\GetAllUseCase;
use App\Application\UseCase\User\GetUserUseCase;
use App\Application\UseCase\User\ImportUsersUseCase;
use App\Application\UseCase\User\MultiUpdateUseCase;
use App\Application\UseCase\User\UpdateUserUseCase;
use App\Domain\Dto\User\ImportDto as DomainImportDto;
use App\Domain\Dto\User\MultiUpdateDto;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Helper\HString;
use App\Presentation\Web\Dto\User\ImportUsersDto;
use App\Presentation\Web\Dto\User\UpdateUsersDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedUsersInfo;
use App\Presentation\Web\Response\Model\LiteUser;
use App\Presentation\Web\Response\Model\UpdatedUsersInfo;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use App\Presentation\Web\Service\DataExport\FileDataExportFactoryInterface;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Throwable;

class UserController extends BaseController
{
    /** Создание пользователя */
    #[Route('/user', 'create-full-user', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function create(
        #[MapRequestPayload('json')]
        CreateFullUserDto $dto,
        CreateUserUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                User::fromUser($user),
            ),
        );
    }

    /** Обновление пользователя */
    #[Route('/users/{id}', 'update-full-user', requirements: ['id' => Requirement::UUID], methods: ['PUT'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function update(
        Uuid $id,
        #[MapRequestPayload('json')]
        UpdateUserDto $dto,
        UpdateUserUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($id, $dto, $this->getUser()->getUser());
        return Response::success(
            new SuccessResponse(
                User::fromUser($user),
            ),
        );
    }

    /** Получить всех пользователей с пагинацией и сортировкой */
    #[Route('/users', 'get-all-users', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessPaginationResponse(LiteUser::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function getAll(
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllUsersDto $dto = new GetAllUsersDto(),
    ): JsonResponse {
        if ($this->getUser()->getUser()->isStudentLeader()) {
            $roles = [RoleEnum::Student->value];
            $groupIds = [$this->getUser()->getUser()->getData()?->getGroup()->getGroupId()->toRfc4122()];
            if (in_array(RoleEnum::Teacher->value, $dto->roles ?? [])) {
                $roles = [RoleEnum::Teacher->value];
                $groupIds = [];
            }
            $dto = new GetAllUsersDto(
                $roles,
                $dto->name,
                $dto->email,
                false,
                UserStatusEnum::Active->value,
                $groupIds,
                count($groupIds) > 0,
                $dto->created_from,
                $dto->created_to,
                $dto->sort_by,
                $dto->sort_type,
                $dto->offset,
                $dto->limit,
            );
        }
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    LiteUser::fromUser(...),
                ),
            ),
        );
    }

    /** Получить пользователя */
    #[Route('/users/{id}', 'get-user', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function getById(
        Uuid $id,
        GetUserUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($id);
        if ($user === null) {
            throw ErrorException::new('Пользователь не найден', 404);
        }
        return Response::success(new SuccessResponse(User::fromUser($user)));
    }

    /** Экспорт пользователей в файл */
    #[Route('/users/export/{exportType<xlsx|csv>}', 'users-export-to-file', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserExport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\FileResponse(['xlsx', 'csv'])]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function export(
        string $exportType,
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        FileDataExportFactoryInterface $dataExportFactory,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllUsersDto $dto = new GetAllUsersDto(),
    ): BinaryFileResponse|JsonResponse {
        try {
            $dataExportFactory->setLogger($logger);
            $dataExport = $dataExportFactory->get($exportType);
            if (!is_dir("$projectDir/export/$exportType")) {
                mkdir("$projectDir/export/$exportType", 0777, true);
            }
            $exportFileName = 'users_'
                . (new DateTimeImmutable())->format('Y-m-d H:i:s')
                . ".$exportType";
            $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
            $dataExport->setFile($fullExportFileName);
        } catch (Throwable $e) {
            $logger->error($e);
            return Response::notFound();
        }

        if ($this->getUser()->getUser()->isStudentLeader()) {
            $roles = [RoleEnum::Student->value];
            $groupIds = [$this->getUser()->getUser()->getData()?->getGroup()->getGroupId()->toRfc4122()];
            if (in_array(RoleEnum::Teacher->value, $dto->roles ?? [])) {
                $roles = [RoleEnum::Teacher->value];
                $groupIds = [];
            }
            $dto = new GetAllUsersDto(
                $roles,
                $dto->name,
                $dto->email,
                false,
                UserStatusEnum::Active->value,
                $groupIds,
                count($groupIds) > 0,
                $dto->created_from,
                $dto->created_to,
                $dto->sort_by,
                $dto->sort_type,
                $dto->offset,
                $dto->limit,
            );
        }
        $dataProvider = $useCase->execute($dto, true);
        if ($dataProvider->getTotal() === 0) {
            return Response::notFound();
        }

        $rows = [
            ['Почта', 'Фамилия', 'Имя', 'Отчество', 'Группа'],
        ];
        foreach ($dataProvider->getItems() as $user) {
            $rows[] = [
                $user->getEmail()->getEmail(),
                $user->getData()?->getLastName(),
                $user->getData()?->getFirstName(),
                $user->getData()?->getPatronymic(),
                $user->getData()?->getGroup()?->getGroup()->getName(),
            ];
        }

        if ($dataExport->exportArray($rows)) {
            return $this->file($fullExportFileName, str_replace(':', '.', HString::rusToEng($exportFileName)))
                ->deleteFileAfterSend();
        }
        return Response::error(
            new ErrorResponse(
                new Error(
                    ErrorSlugEnum::InternalServerError->getSlug(),
                    'Не удалось отправить файл',
                ),
            ),
        );
    }

    /** Массовое обновление пользователей */
    #[Route('/users/update', 'users-update-all', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserUpdateAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\ImportRequestBody(UpdateUsersDto::class)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    #[LOA\SuccessResponse(UpdatedUsersInfo::class)]
    public function updateMulti(
        LoggerInterface $logger,
        MultiUpdateUseCase $useCase,
        #[MapRequestPayload]
        UpdateUsersDto $dto = new UpdateUsersDto(),
        #[MapUploadedFile]
        UploadedFile|array $file = [],
    ): JsonResponse {
        if (is_array($file)) {
            return Response::validation(
                new ValidationResponse([
                    'file' => [
                        new Error(
                            ErrorSlugEnum::WrongField->getSlug(),
                            'Не удалось прочитать файл',
                        ),
                    ],
                ]),
            );
        }
        $useCase->setLogger($logger);
        $updated = $useCase->execute(
            new MultiUpdateDto(
                $file->getPathname(),
                $dto->headers_in_first_row,
                $dto->email_col,
                $dto->last_name_col,
                $dto->first_name_col,
                $dto->patronymic_col,
                $dto->group_name_col,
            ),
        );
        return Response::success(
            new SuccessResponse(
                new UpdatedUsersInfo($updated),
            ),
        );
    }

    #[Route('/users/import', 'users-import', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\ImportRequestBody(ImportUsersDto::class)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    #[LOA\SuccessResponse(CreatedUsersInfo::class)]
    public function import(
        #[MapRequestPayload]
        ImportUsersDto $dto,
        LoggerInterface $logger,
        ImportUsersUseCase $useCase,
        UrlGeneratorInterface $urlGenerator,
        #[MapUploadedFile]
        UploadedFile|array $file = [],
    ): JsonResponse {
        if (is_array($file)) {
            return Response::validation(
                new ValidationResponse([
                    'file' => [
                        new Error(
                            ErrorSlugEnum::WrongField->getSlug(),
                            'Не удалось прочитать файл',
                        ),
                    ],
                ]),
            );
        }

        $useCase->setLogger($logger);
        $createdInfo = $useCase->execute(
            new DomainImportDto(
                $file->getPathname(),
                RoleEnum::from($dto->for_role),
                $dto->headers_in_first_row,
                $dto->last_name_col,
                $dto->first_name_col,
                $dto->patronymic_col,
                $dto->group_name_col,
                $dto->password,
                $dto->email_col,
            ),
        );
        $allDto = $createdInfo->getGetAllUsersDto();
        $urlParameters = [
            'roles' => $allDto->getRoles() !== null
                ? array_map(
                    fn(RoleEnum $el) => $el->value,
                    $allDto->getRoles(),
                )
                : null,
            'name' => $allDto->getName(),
            'email' => $allDto->getEmail(),
            'deleted' => $allDto->getDeleted(),
            'status' => $allDto->getStatus()?->value,
            'group_ids' => $allDto->getGroupIds() !== null
                ? array_map(
                    fn(Uuid $el) => $el->toRfc4122(),
                    $allDto->getGroupIds(),
                )
                : null,
            'with_group' => $allDto->getWithGroup(),
            'created_from' => $allDto->getCreatedFrom()?->format(DATE_RFC3339),
            'created_to' => $allDto->getCreatedTo()?->format(DATE_RFC3339),
            'sort_by' => $allDto->getSortBy(),
            'sort_type' => $allDto->getSortType()?->value,
            'offset' => $allDto->getOffset(),
            'limit' => $allDto->getLimit(),
        ];
        $urlParameters = array_filter($urlParameters);
        return Response::success(
            new SuccessResponse(
                new CreatedUsersInfo(
                    $createdInfo->getCount(),
                    $urlGenerator->generate(
                        'get-all-users',
                        $urlParameters,
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    ),
                    $urlGenerator->generate(
                        'users-export-to-file',
                        [
                            'exportType' => 'xlsx',
                            ...$urlParameters,
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL,
                    ),
                ),
            ),
        );
    }

    /** Удаление пользователя */
    #[Route('/users/{id}', 'delete-user', requirements: ['id' => Requirement::UUID], methods: ['DELETE'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessResponse('boolean')]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(403)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function delete(
        Uuid $id,
        DeleteUserUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $useCase->execute($id, $this->getUser()->getUser());
        return Response::success(new SuccessResponse(true));
    }
}
