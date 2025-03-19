<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\Survey\GetSurveyByIdUseCase;
use App\Application\UseCase\SurveyStat\GenerateForSurveyUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[AsCommand('survey:generate-stat')]
class SurveyGenerateStatCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GetSurveyByIdUseCase $surveyByIdUseCase,
        private GenerateForSurveyUseCase $generateForSurveyUseCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): SurveyGenerateStatCommand
    {
        $this->logger = $logger;
        $this->surveyByIdUseCase->setLogger($logger);
        $this->generateForSurveyUseCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Генерирует статистику по опросу')
            ->addArgument(
                'survey_id',
                InputArgument::REQUIRED,
                'ID опроса',
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $surveyId = $input->getArgument('survey_id');
        try {
            $surveyId = new Uuid($surveyId);
        } catch (Throwable) {
            $this->io->error('Некорректный uuid');
            return self::INVALID;
        }

        $survey = $this->surveyByIdUseCase->execute($surveyId);
        if ($survey === null) {
            $this->io->error('Опрос не найден');
            return self::INVALID;
        }

        try {
            $this->generateForSurveyUseCase->execute($survey);
        } catch (Throwable $e) {
            $this->logger->error($e);
            $this->io->error('Не удалось обновить статистику');
            return self::FAILURE;
        }
        $this->io->success('Статистка успешно обновлена');
        return self::SUCCESS;
    }
}
