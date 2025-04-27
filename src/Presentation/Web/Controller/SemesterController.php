<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Semester\CreateSemesterDto;
use App\Application\Dto\Semester\GetAllSemestersDto;
use App\Application\UseCase\Semester\CreateSemestersUseCase;
use App\Application\UseCase\Semester\CreateSemesterUseCase;
use App\Application\UseCase\Semester\GetAllSemestersUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\CreatedSemestersInfo;
use App\Presentation\Web\Response\Model\FullSemester;
use App\Presentation\Web\Response\Model\Semester;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SemesterController extends BaseController
{
    #[Route('/semester', name: 'create-semester', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SemesterCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('semesters')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessResponse(Semester::class)]
    public function create(
        LoggerInterface $logger,
        CreateSemesterUseCase $useCase,
        #[MapRequestPayload]
        CreateSemesterDto $dto,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $semester = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(Semester::fromSemester($semester)),
        );
    }

    #[Route('/semesters', name: 'create-semester-multi', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SemesterCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('semesters')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessResponse(CreatedSemestersInfo::class)]
    public function createMulti(
        LoggerInterface $logger,
        CreateSemestersUseCase $useCase,
        #[MapRequestPayload(type: CreateSemesterDto::class)]
        array $dtos,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $created = $useCase->execute($dtos);
        return Response::success(
            new SuccessResponse(new CreatedSemestersInfo($created)),
        );
    }

    #[Route('/semesters', name: 'get-all-semesters', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED', statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('semesters')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessPaginationResponse(FullSemester::class)]
    public function getAll(
        LoggerInterface $logger,
        GetAllSemestersUseCase $useCase,
        #[MapQueryString]
        GetAllSemestersDto $dto = new GetAllSemestersDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    FullSemester::fromSemester(...),
                ),
            ),
        );
    }
}
