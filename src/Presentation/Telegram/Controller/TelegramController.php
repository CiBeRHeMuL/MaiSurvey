<?php

namespace App\Presentation\Telegram\Controller;

use AndrewGos\TelegramBot\Telegram;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class TelegramController extends AbstractController
{
    #[Route('/telegram/default', methods: ['POST'])]
    #[LOA\SuccessResponse('boolean')]
    public function index(
        Telegram $telegram,
        LoggerInterface $logger,
    ): JsonResponse {
        try {
            $telegram->getUpdateHandler()->handle();
        } catch (Throwable $e) {
            $logger->error($e->getMessage());
        }

        return Response::success(new SuccessResponse(true));
    }
}
