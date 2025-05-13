<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Me\UpdateMeDto;
use App\Application\UseCase\Me\DeleteMeUseCase;
use App\Application\UseCase\Me\GetMeUseCase;
use App\Application\UseCase\Me\UpdateMeUseCase;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Me;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED', statusCode: 404, exceptionCode: 404)]
class MeController extends BaseController
{
    /** Информация обо мне. */
    #[Route('/me', 'get-me', methods: ['GET'])]
    #[OA\Tag('me')]
    #[LOA\SuccessResponse(Me::class)]
    #[LOA\ErrorResponse]
    #[LOA\ErrorResponse(500)]
    public function me(GetMeUseCase $useCase): JsonResponse
    {
        return Response::success(
            new SuccessResponse(
                Me::fromMe($useCase->execute($this->getUser()->getUser())),
            ),
        );
    }

    /** Обновить себя. */
    #[Route('/me', 'update-me', methods: ['PUT'])]
    #[OA\Tag('me')]
    #[LOA\SuccessResponse(Me::class)]
    #[LOA\ErrorResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function update(
        LoggerInterface $logger,
        UpdateMeUseCase $useCase,
        #[MapRequestPayload('json')]
        UpdateMeDto $dto,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $me = $useCase->execute(
            $this->getUser()->getUser(),
            $dto,
        );
        return Response::success(
            new SuccessResponse(
                Me::fromMe($me),
            ),
        );
    }

    /** Удалить себя. */
    #[Route('/me', 'delete-me', methods: ['DELETE'])]
    #[OA\Tag('me')]
    #[LOA\SuccessResponse('boolean')]
    #[LOA\ErrorResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function delete(
        LoggerInterface $logger,
        DeleteMeUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $useCase->execute($this->getUser()->getUser());
        return Response::success(new SuccessResponse(true));
    }
}
