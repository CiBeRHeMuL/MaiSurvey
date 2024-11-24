<?php

namespace App\Presentation\Web\Controller;

use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED')]
class MeController extends BaseController
{
    /** Информация обо мне. */
    #[Route('/me', 'get-me', methods: ['GET'])]
    #[OA\Tag('me')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse]
    #[LOA\CriticalResponse]
    public function me(): JsonResponse
    {
        return Response::success(
            new SuccessResponse(
                User::fromUser($this->getUser()->getUser()),
            ),
        );
    }
}
