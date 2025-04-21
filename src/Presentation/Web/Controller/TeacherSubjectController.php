<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\TeacherSubject\GetAllTeacherSubjectsDto;
use App\Application\Dto\TeacherSubject\GetMyTeacherSubjectsDto;
use App\Application\UseCase\TeacherSubject\GetAllUseCase;
use App\Application\UseCase\TeacherSubject\GetMyUseCase;
use App\Application\UseCase\TeacherSubject\ImportTeacherSubjectsUseCase;
use App\Domain\Dto\TeacherSubject\ImportDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\TeacherSubject\ImportTeacherSubjectsDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedTeacherSubjectsInfo;
use App\Presentation\Web\Response\Model\MyTeacherSubject;
use App\Presentation\Web\Response\Model\TeacherSubject;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
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

    /** Импорт предметов для преподавателей */
    #[Route('/teacher-subjects/import', 'import-teacher-subjects', methods: ['POST'])]
    #[IsGranted(PermissionEnum::TeacherSubjectImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('teacher-subjects')]
    #[LOA\ImportRequestBody(ImportTeacherSubjectsDto::class)]
    #[LOA\SuccessResponse(CreatedTeacherSubjectsInfo::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function import(
        ImportTeacherSubjectsUseCase $useCase,
        LoggerInterface $logger,
        #[MapRequestPayload]
        ImportTeacherSubjectsDto $dto = new ImportTeacherSubjectsDto(),
        #[MapUploadedFile]
        UploadedFile|array $file = [],
    ): JsonResponse {
        if (is_array($file)) {
            return Response::validation(
                new ValidationResponse([
                    'file' => [
                        new Error(
                            ErrorSlugEnum::WrongField->getSlug(),
                            'Не удалось прочитать файл',
                        ),
                    ],
                ]),
            );
        }

        $useCase->setLogger($logger);
        $created = $useCase->execute(
            new ImportDto(
                $file->getPathname(),
                $dto->headers_in_first_row,
                $dto->email_col,
                $dto->subject_col,
                $dto->type_col,
                $dto->year_col,
                $dto->semester_col,
                $dto->skip_if_exists,
            ),
        );
        return Response::success(
            new SuccessResponse(
                new CreatedTeacherSubjectsInfo($created),
            ),
        );
    }
}
