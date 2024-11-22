<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Group\GetAllGroupsDto;
use App\Application\UseCase\Group\GetAllUseCase;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Group;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GroupController extends BaseController
{
    /** Получить список групп с фильтрацией и пагинацией. */
    #[Route('/groups', name: 'get-all-groups', methods: ['GET'])]
    #[OA\Tag('groups')]
    #[LOA\CriticalResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse]
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
}
