<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\Survey\CloseExpiredSurveysUseCase;
use App\Application\UseCase\Survey\GetSurveysByIdsUseCase;
use App\Application\UseCase\SurveyStat\GenerateForSurveysUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

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
        try {
            $closed = $this->useCase->execute();
            $this->io->success(sprintf('Закрыто %d истекших опросов', $closed));
        } catch (Throwable $e) {
            $this->logger->error($e);
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
