<?php

namespace App\Presentation\Web\Listener;

use App\Domain\Exception\ErrorException;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\Response\Model\Common\CriticalResponse;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener(event: ExceptionEvent::class, priority: -1)]
class ErrorResponseListener
{
    public function __construct(
        #[Autowire('%kernel.debug%')]
        private bool $debug,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if ($e instanceof HttpException && $e->getStatusCode() !== 500) {
            $code = HttpStatusCodeEnum::tryFrom($e->getStatusCode()) ?? HttpStatusCodeEnum::InternalServerError;
            $message = $e->getMessage();
            if ($e->getCode() !== 0) {
                $messageCode = HttpStatusCodeEnum::tryFrom($e->getCode());
                if ($messageCode !== null) {
                    $message = ErrorSlugEnum::{$code->name}->getSlug();
                }
            }
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(ErrorSlugEnum::{$code->name}->getSlug(), $message),
                    ),
                    $code,
                ),
            );
        } elseif ($e instanceof ErrorException) {
            $code = HttpStatusCodeEnum::tryFrom($e->getCode()) ?? HttpStatusCodeEnum::InternalServerError;
            $event->setResponse(
                Response::error(
                    new ErrorResponse(
                        new Error(ErrorSlugEnum::{$code->name}->getSlug(), $e->getMessage()),
                    ),
                    $code,
                ),
            );
        } else {
            $this->logger->error($e);
            if ($this->debug) {
                $event->setResponse(Response::critical(new CriticalResponse($e)));
            } else {
                $event->setResponse(
                    Response::error(
                        new ErrorResponse(
                            new Error(
                                ErrorSlugEnum::InternalServerError->getSlug(),
                                'Internal service error! Please, contact with IT!',
                            ),
                        ),
                        HttpStatusCodeEnum::InternalServerError,
                    ),
                );
            }
        }
        $event->stopPropagation();
    }
}
