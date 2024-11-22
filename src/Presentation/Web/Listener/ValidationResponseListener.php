<?php

namespace App\Presentation\Web\Listener;

use App\Domain\Exception\ValidationException;
use App\Presentation\Web\Response\Model\Common\ValidationResponse;
use App\Presentation\Web\Response\Response;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: ExceptionEvent::class, priority: 10)]
class ValidationResponseListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if ($e instanceof UnprocessableEntityHttpException || $e instanceof HttpException && $e->getStatusCode() === 422) {
            $ve = $e->getPrevious();
            if ($ve instanceof ValidationFailedException) {
                $violations = $ve->getViolations();
                $event->setResponse(
                    Response::validation(
                        ValidationResponse::fromViolationList($violations),
                    ),
                );
            }
        } elseif ($e instanceof ValidationException) {
            $errors = $e->getErrors();
            $event->setResponse(
                Response::validation(
                    ValidationResponse::fromValidationErrors($errors),
                ),
            );
        }
    }
}
