<?php

namespace App\Presentation\Web\Listener;

use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Response;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: RequestEvent::class, priority: 10000)]
class RateLimiterListener
{
    public function __construct(
        private RateLimiterFactory $apiLimiter,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $limiter = $this->apiLimiter->create($event->getRequest()->getClientIp());

        if ($limiter->consume(1)->isAccepted() === false) {
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(
                            ErrorSlugEnum::TooManyRequests->getSlug(),
                            ErrorSlugEnum::TooManyRequests->getSlug(),
                        ),
                    ),
                    HttpStatusCodeEnum::TooManyRequests,
                ),
            );
            $event->stopPropagation();
        }
    }
}
