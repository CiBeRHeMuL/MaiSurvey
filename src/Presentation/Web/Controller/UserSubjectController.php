<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\UserSubject\GetAllDto;
use App\Application\Dto\UserSubject\GetMyDto;
use App\Application\UseCase\UserSubject\GetAllUseCase;
use App\Application\UseCase\UserSubject\GetMyUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\MyUserSubject;
use App\Presentation\Web\Response\Model\UserSubject;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSubjectController extends BaseController
{
    /** Список предметов пользователей с пагинацией и фильтрацией */
    #[Route('/user-subjects', 'get-all-user-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserSubjectViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('user-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(UserSubject::class)]
    public function getAll(
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        #[MapQueryString]
        GetAllDto $dto = new GetAllDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    UserSubject::fromUserSubject(...),
                ),
            ),
        );
    }

    /** Список моих предметов с пагинацией и фильтрацией */
    #[Route('/user-subjects/my', 'get-my-user-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserSubjectViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('user-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(MyUserSubject::class)]
    public function getMy(
        LoggerInterface $logger,
        GetMyUseCase $useCase,
        #[MapQueryString]
        GetMyDto $dto = new GetMyDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    MyUserSubject::fromUserSubject(...),
                ),
            ),
        );
    }
}
