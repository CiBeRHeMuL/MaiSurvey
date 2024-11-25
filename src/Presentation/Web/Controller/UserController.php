<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404)]
class UserController extends BaseController
{
    /** Создание пользователя */
    #[Route('/user', 'create-full-user', methods: ['POST'])]
    #[OA\Tag('user')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\CriticalResponse]
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
}
