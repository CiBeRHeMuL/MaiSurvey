<?php

namespace App\Presentation\Web\Controller;

use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\RoleEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Role;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RoleController extends BaseController
{
    #[Route('/roles', 'get-all-roles', methods: ['GET'])]
    #[IsGranted(PermissionEnum::RoleViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('roles')]
    #[LOA\SuccessPaginationResponse(Role::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    public function getAll(): JsonResponse
    {
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                new PaginatedData(array_map(
                    Role::fromRole(...),
                    RoleEnum::cases(),
                )),
            ),
        );
    }
}
