<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Group\CreateGroupDto;
use App\Application\Dto\Group\GetAllGroupsDto;
use App\Application\UseCase\Group\CreateUseCase;
use App\Application\UseCase\Group\GetAllUseCase;
use App\Application\UseCase\Group\ImportUseCase;
use App\Domain\Dto\Group\ImportDto as DomainImportDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\Group\ImportGroupsDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedGroupsInfo;
use App\Presentation\Web\Response\Model\Group;
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

class GroupController extends BaseController
{
    /** Получить список групп с фильтрацией и пагинацией. */
    #[Route('/groups', name: 'get-all-groups', methods: ['GET'])]
    #[OA\Tag('groups')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessPaginationResponse(Group::class)]
    public function getAll(
        GetAllUseCase $useCase,
        LoggerInterface $logger,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllGroupsDto $dto = new GetAllGroupsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    Group::fromGroup(...),
                ),
            ),
        );
    }

    /** Создать группу */
    #[Route('/group', 'create-group', methods: ['POST'])]
    #[IsGranted(PermissionEnum::GroupCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('groups')]
    #[LOA\SuccessResponse(Group::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function create(
        #[MapRequestPayload('json')]
        CreateGroupDto $dto,
        LoggerInterface $logger,
        CreateUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $group = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                Group::fromGroup($group),
            ),
        );
    }

    /** Импорт групп */
    #[Route('/groups/import', 'import-groups', methods: ['POST'])]
    #[IsGranted(PermissionEnum::GroupImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('groups')]
    #[LOA\ImportRequestBody(ImportGroupsDto::class)]
    #[LOA\SuccessResponse(CreatedGroupsInfo::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function import(
        ImportUseCase $useCase,
        LoggerInterface $logger,
        #[MapRequestPayload('form')]
        ImportGroupsDto $dto,
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
                $dto->headers_in_first_row,
                $dto->name_col,
            ),
        );
        return Response::success(
            new SuccessResponse(
                new CreatedGroupsInfo($created),
            ),
        );
    }
}
