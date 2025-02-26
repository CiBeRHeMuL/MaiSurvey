<?php

namespace App\Presentation\Web\Controller;

use App\Application\UseCase\SurveyStat\GetSurveyStatByIdUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\SurveyStat;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

class SurveyStatController extends BaseController
{
    #[Route('/surveys/{id}/stat', 'get-survey-stat-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyStatView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-stats')]
    #[LOA\SuccessResponse(SurveyStat::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ErrorResponse(500)]
    public function getById(
        Uuid $id,
        GetSurveyStatByIdUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $stat = $useCase->execute($id);
        if ($stat === null) {
            return Response::notFound();
        } else {
            return Response::success(
                new SuccessResponse(
                    SurveyStat::fromStat($stat),
                ),
            );
        }
    }
}
