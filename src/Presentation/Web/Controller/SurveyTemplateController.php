<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\SurveyTemplate\Create\CreateSurveyTemplateDto;
use App\Application\Dto\SurveyTemplate\GetAllSurveyTemplatesDto;
use App\Application\UseCase\SurveyTemplate\CreateSurveyTemplateUseCase;
use App\Application\UseCase\SurveyTemplate\GetAllSurveyTemplatesUseCase;
use App\Application\UseCase\SurveyTemplate\GetSurveyTemplateByIdUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\LiteSurveyTemplate;
use App\Presentation\Web\Response\Model\SurveyTemplate;
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

class SurveyTemplateController extends BaseController
{
    /** Список шаблонов опросов */
    #[Route('/survey-templates', 'survey-template-get-all', methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyTemplateViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-templates')]
    #[LOA\SuccessPaginationResponse(LiteSurveyTemplate::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ValidationResponse]
    public function getAll(
        LoggerInterface $logger,
        GetAllSurveyTemplatesUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllSurveyTemplatesDto $dto = new GetAllSurveyTemplatesDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $provider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $provider,
                    LiteSurveyTemplate::fromSurveyTemplate(...),
                ),
            ),
        );
    }

    /** Получить шаблон опроса по id */
    #[Route('/survey-templates/{id}', 'survey-template-get-my-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyTemplateViewAll->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-templates')]
    #[LOA\SuccessResponse(SurveyTemplate::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getById(
        Uuid $id,
        LoggerInterface $logger,
        GetSurveyTemplateByIdUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $template = $useCase->execute($id);
        if ($template === null) {
            return Response::notFound();
        } else {
            return Response::success(
                new SuccessResponse(
                    SurveyTemplate::fromSurveyTemplate($template),
                ),
            );
        }
    }

    /** Создать шаблон опроса */
    #[Route('/survey-template', 'create-survey-template', methods: ['POST'])]
    #[IsGranted(PermissionEnum::SurveyTemplateCreate->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-templates')]
    #[LOA\SuccessResponse(SurveyTemplate::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function create(
        LoggerInterface $logger,
        #[MapRequestPayload]
        CreateSurveyTemplateDto $dto,
        CreateSurveyTemplateUseCase $useCase,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $template = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                SurveyTemplate::fromSurveyTemplate($template),
            ),
        );
    }
}
