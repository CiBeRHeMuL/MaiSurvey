<?php

namespace App\Presentation\Console\Listener;

use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ConsoleErrorEvent::class, priority: -1)]
class ErrorListener
{
    public function __invoke(ConsoleErrorEvent $event): void
    {
        $io = new SymfonyStyle($event->getInput(), $event->getOutput());
        $io->error($event->getError()->getMessage());
    }
}
