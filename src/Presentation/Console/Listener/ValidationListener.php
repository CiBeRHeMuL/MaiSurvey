<?php

namespace App\Presentation\Console\Listener;

use App\Domain\Exception\ValidationException;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: ConsoleErrorEvent::class, priority: 10)]
class ValidationListener
{
    public function __invoke(ConsoleErrorEvent $event): void
    {
        $e = $event->getError();
        $io = new SymfonyStyle($event->getInput(), $event->getOutput());
        if ($e instanceof ValidationException) {
            $headers = [];
            $errors = [];
            foreach ($e->getErrors() as $error) {
                $headers[] = $error->getField();
                $errors[] = $error->getMessage();
            }
            $io->horizontalTable($headers, [$errors]);
        } elseif ($e instanceof ValidationFailedException) {
            $headers = [];
            $errors = [];
            foreach ($e->getViolations() as $violation) {
                $headers[] = $violation->getPropertyPath();
                $errors[] = $violation->getMessage();
            }
            $io->horizontalTable($headers, [$errors]);
        }
    }
}
