<?php

namespace App\Presentation\Web\Controller;

use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends BaseController
{
    /** Проверка того, что сервер в рабочем состоянии */
    #[Route('/health', 'check-health', methods: ['GET'])]
    #[OA\Get(security: [])]
    #[OA\Tag('health')]
    #[LOA\SuccessResponse]
    public function health(): JsonResponse
    {
        return Response::success();
    }
}
