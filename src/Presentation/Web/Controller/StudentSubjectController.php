<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\StudentSubject\GetAllStudentSubjectsDto;
use App\Application\Dto\StudentSubject\GetMyStudentSubjectsDto;
use App\Application\UseCase\StudentSubject\GetAllUseCase;
use App\Application\UseCase\StudentSubject\GetMyUseCase;
use App\Application\UseCase\StudentSubject\ImportByGroupsUseCase;
use App\Application\UseCase\StudentSubject\ImportUseCase;
use App\Domain\Dto\StudentSubject\ImportByGroupsDto;
use App\Domain\Dto\StudentSubject\ImportDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\StudentSubject\ImportSSByGroupsDto;
use App\Presentation\Web\Dto\StudentSubject\ImportStudentSubjectsDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Model\CreatedStudentSubjectsInfo;
use App\Presentation\Web\Response\Model\MyStudentSubject;
use App\Presentation\Web\Response\Model\StudentSubject;
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

    /** Импорт предметов для студентов индивидуально */
    #[Route('/student-subjects/import', 'student-subjects-import-for-student', methods: ['POST'])]
    #[IsGranted(PermissionEnum::StudentSubjectImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('student-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessResponse(CreatedStudentSubjectsInfo::class)]
    public function import(
        LoggerInterface $logger,
        ImportUseCase $useCase,
        #[MapRequestPayload]
        ImportStudentSubjectsDto $dto = new ImportStudentSubjectsDto(),
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

        $onlyForGroupId = null;
        if ($this->getUser()->getUser()->isStudentLeader()) {
            $onlyForGroupId = $this->getUser()->getUser()->getGroup()->getId();
        }

        $useCase->setLogger($logger);
        $result = $useCase->execute(
            new ImportDto(
                $file->getPathname(),
                $dto->headers_in_first_row,
                $dto->student_email_col,
                $dto->teacher_email_col,
                $dto->subject_col,
                $dto->type_col,
                $dto->year_col,
                $dto->semester_col,
                $dto->skip_if_exists,
                $onlyForGroupId,
            ),
        );

        return Response::success(
            new SuccessResponse(
                new CreatedStudentSubjectsInfo(
                    $result->getCreated(),
                    $result->getSkipped(),
                ),
            ),
        );
    }

    /** Импорт предметов для студентов по группам */
    #[Route('/student-subjects/import/groups', 'student-subjects-import-for-groups', methods: ['POST'])]
    #[IsGranted(PermissionEnum::StudentSubjectImport->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('student-subjects')]
    #[LOA\ErrorResponse(500)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\SuccessResponse(CreatedStudentSubjectsInfo::class)]
    public function importByGroups(
        LoggerInterface $logger,
        ImportByGroupsUseCase $useCase,
        #[MapRequestPayload]
        ImportSSByGroupsDto $dto = new ImportSSByGroupsDto(),
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

        $onlyForGroupId = null;
        if ($this->getUser()->getUser()->isStudentLeader()) {
            $onlyForGroupId = $this->getUser()->getUser()->getGroup()->getId();
        }

        $useCase->setLogger($logger);
        $result = $useCase->execute(
            new ImportByGroupsDto(
                $file->getPathname(),
                $dto->headers_in_first_row,
                $dto->group_name_col,
                $dto->teacher_email_col,
                $dto->subject_col,
                $dto->type_col,
                $dto->year_col,
                $dto->semester_col,
                $dto->skip_if_exists,
                $onlyForGroupId,
            ),
        );

        return Response::success(
            new SuccessResponse(
                new CreatedStudentSubjectsInfo(
                    $result->getCreated(),
                    $result->getSkipped(),
                ),
            ),
        );
    }
}
