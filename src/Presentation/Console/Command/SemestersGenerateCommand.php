<?php

namespace App\Presentation\Console\Command;

use App\Application\Dto\Semester\CreateSemesterDto;
use App\Application\Dto\Semester\GetAllSemestersDto;
use App\Application\UseCase\Semester\CreateSemestersUseCase;
use App\Application\UseCase\Semester\GetAllSemestersUseCase;
use App\Domain\Helper\HArray;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('semesters:generate')]
class SemestersGenerateCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GetAllSemestersUseCase $allSemestersUseCase,
        private CreateSemestersUseCase $createSemestersUseCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->allSemestersUseCase->setLogger($logger);
        $this->createSemestersUseCase->setLogger($logger);
        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $existing = $this
            ->allSemestersUseCase
            ->execute(new GetAllSemestersDto('year'));
        $this->io->info(sprintf('Найдено %d существующих семестров', $existing->getTotal()));

        $year = (int)(new DateTimeImmutable())->format('Y');
        $fromYear = $year - 10;
        $toYear = $year + 10;

        /** @var array<string, CreateSemesterDto> $newDtos */
        $newDtos = array_map(
            fn(float $v) => new CreateSemesterDto(intval(floor($v)), fmod($v, 1) < 0.5),
            range($fromYear, $toYear, 0.5),
        );
        $newDtos = HArray::index(
            $newDtos,
            fn(CreateSemesterDto $e) => md5("$e->year$e->spring"),
        );
        foreach ($existing->getItems() as $semester) {
            $hash = md5("{$semester->getYear()}{$semester->isSpring()}");
            if (isset($newDtos[$hash])) {
                $this->io->writeln(sprintf('<fg=yellow>Семестр %s уже существует</>', $semester->getName()));
                unset($newDtos[$hash]);
            }
        }

        $created = $this
            ->createSemestersUseCase
            ->execute($newDtos);
        $this->io->success(sprintf('Создано %d семестров с %s года по %s год', $created, $fromYear, $toYear));
        return self::SUCCESS;
    }
}
