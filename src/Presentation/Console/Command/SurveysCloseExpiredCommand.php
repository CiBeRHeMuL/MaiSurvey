<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\Survey\CloseExpiredSurveysUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('surveys:close-expired')]
class SurveysCloseExpiredCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CloseExpiredSurveysUseCase $useCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): SurveysCloseExpiredCommand
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this->setDescription('Закрывает истекшие опросы');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $closed = $this->useCase->execute();
        $this->io->success(sprintf('Закрыто %d истекших опросов', $closed));
        return self::SUCCESS;
    }
}
