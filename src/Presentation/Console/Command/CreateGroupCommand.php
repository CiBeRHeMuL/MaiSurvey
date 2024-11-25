<?php

namespace App\Presentation\Console\Command;

use App\Application\Dto\Group\CreateGroupDto;
use App\Application\UseCase\Group\CreateUseCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('groups:create')]
class CreateGroupCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        private CreateUseCase $useCase,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): CreateGroupCommand
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Создание группы')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Имя группы',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $group = $this->useCase->execute(
            new CreateGroupDto(
                $input->getArgument('name'),
            ),
        );
        $this->io->writeln('<fg=green>Группа успешно создана</>');
        $this->io->horizontalTable(
            ['ID', 'имя'],
            [[$group->getId()->toRfc4122(), $group->getName()]],
        );
        return self::SUCCESS;
    }
}
