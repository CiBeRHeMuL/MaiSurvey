<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\Dto\User\GetAllDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\GetAllUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends BaseController
{
    /** Создание пользователя */
    #[Route('/user', 'create-full-user', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404)]
    #[OA\Tag('user')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function create(
        #[MapRequestPayload]
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
    #[IsGranted(PermissionEnum::UserView->value, statusCode: 404)]
    #[OA\Tag('user')]
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
        GetAllDto $dto = new GetAllDto(),
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
}
