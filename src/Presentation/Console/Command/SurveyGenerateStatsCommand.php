<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\SurveyStat\GenerateForSurveysUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[AsCommand('survey:generate-stats')]
class SurveyGenerateStatsCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GenerateForSurveysUseCase $generateForSurveysUseCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): SurveyGenerateStatsCommand
    {
        $this->logger = $logger;
        $this->generateForSurveysUseCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Генерирует статистику по опросам')
            ->addArgument(
                'survey_ids',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'ID опросов',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $surveyIds = $input->getArgument('survey_ids');
        if ($surveyIds) {
            try {
                array_walk(
                    $surveyIds,
                    function (&$v, $k) {
                        try {
                            $v = new Uuid($v);
                        } catch (Throwable $e) {
                            $this->io->error(sprintf('Некорректный uuid на %d позиции', $k + 1));
                            throw $e;
                        }
                    },
                );
            } catch (Throwable) {
                return self::INVALID;
            }
        }

        try {
            $updated = $this->generateForSurveysUseCase->execute($surveyIds);
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->io->error('Не удалось обновить статистику');
            return self::FAILURE;
        }
        $this->io->success(sprintf('Статистка успешно обновлена по %d опросам', $updated));
        return self::SUCCESS;
    }
}
