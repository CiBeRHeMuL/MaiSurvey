<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\Survey\GetSurveysByIdsUseCase;
use App\Application\UseCase\SurveyStat\GenerateForSurveysUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[AsCommand('surveys:generate-stat')]
class SurveysGenerateStatCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GenerateForSurveysUseCase $generateForSurveysUseCase,
        private GetSurveysByIdsUseCase $surveysByIdsUseCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): SurveysGenerateStatCommand
    {
        $this->logger = $logger;
        $this->generateForSurveysUseCase->setLogger($logger);
        $this->surveysByIdsUseCase->setLogger($logger);
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
        $surveyIds = $input->getArgument('survey_ids') ?: null;
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
            $surveys = $surveyIds !== null
                ? $this->surveysByIdsUseCase->execute($surveyIds, true)
                : null;
            $this->generateForSurveysUseCase->execute($surveys);
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->io->error('Не удалось обновить статистику');
            return self::FAILURE;
        }
        $this->io->success('Статистка успешно обновлена');
        return self::SUCCESS;
    }
}
