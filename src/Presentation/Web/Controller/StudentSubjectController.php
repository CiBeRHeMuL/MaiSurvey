<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Application\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Application\UseCase\StudentSubject\GetAllUseCase;
use App\Application\UseCase\StudentSubject\GetMyUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\MyStudentSubject;
use App\Presentation\Web\Response\Model\StudentSubject;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StudentSubjectController extends BaseController
{
    /** Список предметов студентов с пагинацией и фильтрацией */
    #[Route('/student-subjects', 'get-all-student-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::StudentSubjectViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('student-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(StudentSubject::class)]
    public function getAll(
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllStudentSubjectsDto $dto = new GetAllStudentSubjectsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    StudentSubject::fromStudentSubject(...),
                ),
            ),
        );
    }

    /** Список моих предметов с пагинацией и фильтрацией */
    #[Route('/student-subjects/my', 'get-my-student-subjects', methods: ['GET'])]
    #[IsGranted(PermissionEnum::StudentSubjectViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('student-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessPaginationResponse(MyStudentSubject::class)]
    public function getMy(
        LoggerInterface $logger,
        GetMyUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetMyStudentSubjectsDto $dto = new GetMyStudentSubjectsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    MyStudentSubject::fromStudentSubject(...),
                ),
            ),
        );
    }

    public function import(): JsonResponse
    {

    }
}
