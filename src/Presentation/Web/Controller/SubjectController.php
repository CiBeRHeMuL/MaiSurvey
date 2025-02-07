<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Subject\CreateSubjectDto;
use App\Application\Dto\Subject\GetAllSubjectsDto;
use App\Application\UseCase\Subject\CreateUseCase;
use App\Application\UseCase\Subject\GetAllUseCase;
use App\Application\UseCase\Subject\ImportUseCase;
use App\Domain\Dto\Subject\ImportDto as DomainImportDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\Subject\ImportSubjectsDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedSubjectsInfo;
use App\Presentation\Web\Response\Model\Subject;
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

class SubjectController extends BaseController
{
    /** Получить список предметов с фильтрацией и пагинацией. */
    #[Route('/subjects', name: 'get-all-subjects', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED', statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\SuccessPaginationResponse(Subject::class)]
    public function getAll(
        GetAllUseCase $useCase,
        LoggerInterface $logger,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllSubjectsDto $dto = new GetAllSubjectsDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    Subject::fromSubject(...),
                ),
            ),
        );
    }

    /** Создать предмет */
    #[Route('/subject', 'create-subject', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SubjectCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('subjects')]
    #[LOA\SuccessResponse(Subject::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function create(
        #[MapRequestPayload('json')]
        CreateSubjectDto $dto,
        LoggerInterface $logger,
        CreateUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $subject = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                Subject::fromSubject($subject),
            ),
        );
    }

    /** Импорт предметов */
    #[Route('/subjects/import', 'import-subjects', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SubjectImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('subjects')]
    #[LOa\ImportRequestBody(ImportSubjectsDto::class)]
    #[LOA\SuccessResponse(CreatedSubjectsInfo::class)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(500)]
    public function import(
        ImportUseCase $useCase,
        LoggerInterface $logger,
        #[MapRequestPayload]
        ImportSubjectsDto $dto,
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
            new DomainImportDto(
                $file->getPathname(),
                $dto->headers_in_first_row,
                $dto->name_col,
                $dto->year_col,
                $dto->semester_col,
            ),
        );
        return Response::success(
            new SuccessResponse(
                new CreatedSubjectsInfo($created),
            ),
        );
    }
}
