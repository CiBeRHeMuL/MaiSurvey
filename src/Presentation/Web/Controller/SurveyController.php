<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Survey\Complete\CompleteSurveyDto;
use App\Application\Dto\Survey\Complete\CompleteSurveyItemDto;
use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Dto\Survey\GetMySurveysDto;
use App\Application\UseCase\Survey\CompleteSurveyUseCase;
use App\Application\UseCase\Survey\CreateSurveyUseCase;
use App\Application\UseCase\Survey\GetMySurveyByIdUseCase;
use App\Application\UseCase\Survey\GetMySurveysUseCase;
use App\Domain\Dto\Survey\GetMySurveyByIdDto as DomainGetMySurveyByIdDto;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Dto\Survey\GetMySurveyByIdDto;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
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
}
