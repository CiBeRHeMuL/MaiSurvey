<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\Dto\User\GetAllUsersDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\GetAllUseCase;
use App\Application\UseCase\User\ImportUsersUseCase;
use App\Application\UseCase\User\MultiUpdateUseCase;
use App\Domain\Dto\User\ImportDto as DomainImportDto;
use App\Domain\Dto\User\MultiUpdateDto;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
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
use App\Presentation\Web\Response\Model\CreatedUserDataInfo;
use App\Presentation\Web\Response\Model\CreatedUsersInfo;
use App\Presentation\Web\Response\Model\UpdatedUsersInfo;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use App\Presentation\Web\Service\DataExport\FileDataExportFactoryInterface;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    /** Получить всех пользователей с пагинацией и сортировкой */
    #[Route('/users', 'get-all-users', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('users')]
    #[LOA\SuccessPaginationResponse(User::class)]
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
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    User::fromUser(...),
                ),
            ),
        );
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
            $exportFileName = 'users_' . (string)time() . ".$exportType";
            $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
            $dataExport->setFile($fullExportFileName);
        } catch (Throwable $e) {
            return Response::notFound();
        }

        $dataProvider = $useCase->execute($dto);
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
            return $this->file(
                $fullExportFileName,
                $exportFileName,
            );
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
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    #[LOA\SuccessResponse(CreatedUsersInfo::class)]
    public function import(
        #[MapRequestPayload]
        ImportUsersDto $dto,
        LoggerInterface $logger,
        ImportUsersUseCase $useCase,
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
        $created = $useCase->execute(
            new DomainImportDto(
                $file->getPathname(),
                RoleEnum::from($dto->for_role),
                $dto->headers_in_first_row,
                $dto->last_name_col,
                $dto->first_name_col,
                $dto->patronymic_col,
                $dto->group_name_col,
                $dto->password,
            ),
        );
        return Response::success(
            new SuccessResponse(
                new CreatedUserDataInfo($created),
            ),
        );
    }
}
