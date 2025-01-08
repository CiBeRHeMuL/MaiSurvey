<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\UserData\GetAllUserDataDto;
use App\Application\Dto\UserData\GetAvailableUserDataDto;
use App\Application\UseCase\UserData\GetAllUseCase;
use App\Application\UseCase\UserData\GetAvailableUseCase;
use App\Application\UseCase\UserData\ImportUseCase;
use App\Domain\Dto\UserData\ImportDto as DomainImportDto;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use App\Presentation\Web\Dto\UserData\ImportUserDataDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedUserDataInfo;
use App\Presentation\Web\Response\Model\UserData;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDataController extends BaseController
{
    /** Получить полный список данных пользователей с фильтрацией и пагинацией. */
    #[Route('/user-data/all', name: 'get-all-user-data', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserDataViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('user-data')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessPaginationResponse(UserData::class)]
    public function getAll(
        GetAllUseCase $useCase,
        LoggerInterface $logger,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllUserDataDto $dto = new GetAllUserDataDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    UserData::fromData(...),
                ),
            ),
        );
    }

    /** Получить доступный для привязки список данных пользователей с фильтрацией и пагинацией. */
    #[Route('/user-data/available', name: 'get-available-user-data', methods: ['GET'])]
    #[OA\Tag('user-data')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessPaginationResponse(UserData::class)]
    public function getAvailable(
        GetAvailableUseCase $useCase,
        LoggerInterface $logger,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAvailableUserDataDto $dto = new GetAvailableUserDataDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    UserData::fromData(...),
                ),
            ),
        );
    }

    /** Импорт данных пользователей */
    #[Route('/user-data/import', 'import-user-data', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserDataImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('user-data')]
    #[LOA\ImportRequestBody(ImportUserDataDto::class)]
    #[LOA\SuccessResponse(CreatedUserDataInfo::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function import(
        ImportUseCase $useCase,
        LoggerInterface $logger,
        #[MapRequestPayload('form')]
        ImportUserDataDto $dto,
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
            ),
        );
        return Response::success(
            new SuccessResponse(
                new CreatedUserDataInfo($created),
            ),
        );
    }
}
