<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Group\GetAllGroupsDto;
use App\Application\Dto\UserData\GetAllUserDataDto;
use App\Application\UseCase\UserData\GetAllUseCase;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\UserData;
use App\Presentation\Web\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Presentation\Web\OpenApi\Attribute as LOA;

class UserDataController extends BaseController
{
    /** Получить список данных пользователей с фильтрацией и пагинацией. */
    #[Route('/user-data/all', name: 'get-all-user-data', methods: ['GET'])]
    #[OA\Tag('user-data')]
    #[LOA\CriticalResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse]
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
}
