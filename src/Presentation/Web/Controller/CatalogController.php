<?php

namespace App\Presentation\Web\Controller;

use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Catalog;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends BaseController
{
    #[Route('/catalog', name: 'catalog', methods: ['GET'])]
    #[LOA\SuccessResponse(Catalog::class)]
    public function all(): JsonResponse
    {
        return Response::success(new SuccessResponse(Catalog::generate()));
    }
}
