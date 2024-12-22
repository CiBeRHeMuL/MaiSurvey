<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Application\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Application\UseCase\TeacherSubject\GetAllUseCase;
use App\Application\UseCase\TeacherSubject\GetMyUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\MyTeacherSubject;
use App\Presentation\Web\Response\Model\TeacherSubject;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TeacherSubjectController extends BaseController
{
    /** Список предметов преподавателей с пагинацией и фильтрацией */
    #[Route('/teacher-subjects', 'get-all-teacher-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::TeacherSubjectViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('teacher-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(TeacherSubject::class)]
    public function getAll(
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllTeacherSubjectsDto $dto = new GetAllTeacherSubjectsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    TeacherSubject::fromTeacherSubject(...),
                ),
            ),
        );
    }

    /** Список моих предметов с пагинацией и фильтрацией */
    #[Route('/teacher-subjects/my', 'get-my-teacher-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::TeacherSubjectViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('teacher-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(MyTeacherSubject::class)]
    public function getMy(
        LoggerInterface $logger,
        GetMyUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetMyTeacherSubjectsDto $dto = new GetMyTeacherSubjectsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    MyTeacherSubject::fromTeacherSubject(...),
                ),
            ),
        );
    }
}
