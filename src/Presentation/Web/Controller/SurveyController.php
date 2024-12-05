<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Survey\GetMySurveysDto;
use App\Application\UseCase\Survey\GetMySurveysUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\MySurvey;
use App\Presentation\Web\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SurveyController extends BaseController
{
    #[Route('/surveys/my', 'survey-get-my', methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyViewMy->value, statusCode: 404, exceptionCode: 404)]
    #[LOA\SuccessPaginationResponse(MySurvey::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    public function getMy(
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
                    MySurvey::fromMySurvey(...),
                ),
            ),
        );
    }
}
