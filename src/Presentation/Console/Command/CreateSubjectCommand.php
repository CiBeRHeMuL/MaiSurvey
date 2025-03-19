<?php

namespace App\Presentation\Console\Command;

use App\Application\Dto\Subject\CreateSubjectDto;
use App\Application\UseCase\Subject\CreateUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('subject:create')]
class CreateSubjectCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        private CreateUseCase $useCase,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): CreateSubjectCommand
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Создание предмета')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Название предмета',
            )
            ->addArgument(
                'semester_id',
                InputArgument::REQUIRED,
                'ID семестра',
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $this->useCase->execute(
            new CreateSubjectDto(
                $input->getArgument('name'),
                $input->getArgument('semester_id'),
            ),
        );
        $this->io->writeln('<fg=green>Предмет успешно создана</>');
        $this->io->horizontalTable(
            ['ID', 'Название'],
            [[$group->getId()->toRfc4122(), $group->getName()]],
        );
        return self::SUCCESS;
    }
}
