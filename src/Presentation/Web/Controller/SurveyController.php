<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Survey\Complete\CompleteSurveyDto;
use App\Application\Dto\Survey\Complete\CompleteSurveyItemDto;
use App\Application\Dto\Survey\Create\CreateFromTemplateDto;
use App\Application\Dto\Survey\Create\CreateMSFromTemplateDto;
use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Dto\Survey\Create\CreateSurveyMSDto;
use App\Application\Dto\Survey\GetMySurveysDto;
use App\Application\Dto\Survey\GetSurveysDto;
use App\Application\Dto\Survey\Update\UpdateSurveyDto;
use App\Application\UseCase\Survey\CompleteSurveyUseCase;
use App\Application\UseCase\Survey\CreateSurveyFromTemplateUseCase;
use App\Application\UseCase\Survey\CreateSurveyMSFromTemplateUseCase;
use App\Application\UseCase\Survey\CreateSurveyMSUseCase;
use App\Application\UseCase\Survey\CreateSurveyUseCase;
use App\Application\UseCase\Survey\GetMySurveyByIdUseCase;
use App\Application\UseCase\Survey\GetMySurveysUseCase;
use App\Application\UseCase\Survey\GetSurveyByIdUseCase;
use App\Application\UseCase\Survey\GetSurveysUseCase;
use App\Application\UseCase\Survey\UpdateSurveyUseCase;
use App\Domain\Dto\Survey\GetMySurveyByIdDto as DomainGetMySurveyByIdDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\Survey\GetMySurveyByIdDto;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\FullSurvey;
use App\Presentation\Web\Response\Model\LiteFullSurvey;
use App\Presentation\Web\Response\Model\LiteMySurvey;
use App\Presentation\Web\Response\Model\MySurvey;
use App\Presentation\Web\Response\Model\Survey;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

class SurveyController extends BaseController
{
    #[Route('/surveys/my', 'survey-get-all-my', methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessPaginationResponse(LiteMySurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getAllMy(
        LoggerInterface $logger,
        GetMySurveysUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetMySurveysDto $dto = new GetMySurveysDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    LiteMySurvey::fromMySurvey(...),
                ),
            ),
        );
    }

    #[Route('/surveys/my/{id}', 'survey-get-my-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse(MySurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getMyById(
        Uuid $id,
        LoggerInterface $logger,
        GetMySurveyByIdUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetMySurveyByIdDto $dto = new GetMySurveyByIdDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $survey = $useCase->execute(
            $this->getUser()->getUser(),
            new DomainGetMySurveyByIdDto(
                $id,
                $dto->completed,
                true,
            ),
        );
        if ($survey === null) {
            return Response::notFound();
        } else {
            return Response::success(
                new SuccessResponse(
                    MySurvey::fromMySurvey($survey),
                ),
            );
        }
    }

    /** Пройти опрос */
    #[Route('/surveys/my/{id}/complete', 'survey-complete-my-by-id', requirements: ['id' => Requirement::UUID], methods: ['PUT'])]
    #[IsGranted(PermissionEnum::SurveyComplete->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function complete(
        Uuid $id,
        LoggerInterface $logger,
        CompleteSurveyUseCase $useCase,
        #[MapRequestPayload('json', type: CompleteSurveyItemDto::class)]
        array $answers,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $useCase->execute(
            $this->getUser()->getUser(),
            new CompleteSurveyDto(
                $id,
                $answers,
            ),
        );
        return Response::success();
    }

    /** Создать опрос */
    #[Route('/survey', 'create-survey', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SurveyCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse(Survey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function create(
        LoggerInterface $logger,
        #[MapRequestPayload]
        CreateSurveyDto $dto,
        CreateSurveyUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $survey = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                Survey::fromSurvey($survey),
            ),
        );
    }

    /** Обновить опрос */
    #[Route('/surveys/{id}', 'update-survey', requirements: ['id' => Requirement::UUID], methods: ['PUT'])]
    #[IsGranted(PermissionEnum::SurveyCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse(FullSurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function update(
        Uuid $id,
        LoggerInterface $logger,
        #[MapRequestPayload]
        UpdateSurveyDto $dto,
        UpdateSurveyUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $survey = $useCase->execute($id, $dto);
        return Response::success(
            new SuccessResponse(
                FullSurvey::fromSurvey($survey),
            ),
        );
    }

    /** Создать опрос для нескольких предметов сразу */
    #[Route('/survey/multi-subject', 'create-survey-multi-subject', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SurveyCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessPaginationResponse(Survey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function createMS(
        LoggerInterface $logger,
        #[MapRequestPayload]
        CreateSurveyMSDto $dto,
        CreateSurveyMSUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $surveys = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromArray(
                    array_map(
                        Survey::fromSurvey(...),
                        $surveys,
                    ),
                ),
            ),
        );
    }

    /** Создать опрос из шаблона */
    #[Route('/survey/from-template', 'create-survey-from-template', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SurveyCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse(Survey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function createFromTemplate(
        LoggerInterface $logger,
        #[MapRequestPayload]
        CreateFromTemplateDto $dto,
        CreateSurveyFromTemplateUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $survey = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                Survey::fromSurvey($survey),
            ),
        );
    }

    /** Создать опрос из шаблона для нескольких предметов сразу */
    #[Route('/survey/from-template/multi-subject', 'create-survey-from-template-multi-subject', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SurveyCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessPaginationResponse(Survey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function createFromTemplateMS(
        LoggerInterface $logger,
        #[MapRequestPayload]
        CreateMSFromTemplateDto $dto,
        CreateSurveyMSFromTemplateUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $surveys = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromArray(
                    array_map(
                        Survey::fromSurvey(...),
                        $surveys,
                    ),
                ),
            ),
        );
    }

    #[Route('/surveys/{id}', 'survey-get-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessResponse(FullSurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getById(
        Uuid $id,
        LoggerInterface $logger,
        GetSurveyByIdUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $survey = $useCase->execute($id);
        if ($survey === null) {
            return Response::notFound();
        } else {
            return Response::success(
                new SuccessResponse(
                    FullSurvey::fromSurvey($survey),
                ),
            );
        }
    }

    #[Route('/surveys', 'survey-get-all', methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('surveys')]
    #[LOA\SuccessPaginationResponse(LiteFullSurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getAll(
        LoggerInterface $logger,
        GetSurveysUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetSurveysDto $dto = new GetSurveysDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($dto);
        $withStat = $this->getUser()->getUser()->hasPermission(PermissionEnum::SurveyStatView);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    fn($s) => LiteFullSurvey::fromSurvey($s, $withStat),
                ),
            ),
        );
    }
}
