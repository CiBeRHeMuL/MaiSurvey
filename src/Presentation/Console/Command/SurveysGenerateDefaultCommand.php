<?php

namespace App\Presentation\Console\Command;

use App\Application\Dto\Subject\GetAllSubjectsDto;
use App\Application\Dto\Survey\Create\CommentItemData;
use App\Application\Dto\Survey\Create\CreateItemDto;
use App\Application\Dto\Survey\Create\CreateSurveyDto;
use App\Application\Dto\Survey\Create\RatingItemData;
use App\Application\UseCase\Subject\GetAllUseCase;
use App\Application\UseCase\Survey\CreateSurveysUseCase;
use App\Domain\DataProvider\ProjectionAwareDataProvider;
use App\Domain\Entity\Subject;
use App\Domain\Entity\Survey;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Enum\TeacherSubjectTypeEnum;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('surveys:generate:default')]
class SurveysGenerateDefaultCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private GetAllUseCase $subjectsUseCase,
        private CreateSurveysUseCase $createSurveysUseCase,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): SurveysGenerateDefaultCommand
    {
        $this->logger = $logger;
        $this->subjectsUseCase->setLogger($logger);
        $this->createSurveysUseCase->setLogger($logger);
        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $createItemDtos = [
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените увлекательность подачи материала лектором',
                1,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените увлекательность подачи материала семинаристом',
                2,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените понятность изложения материала лектором',
                3,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените понятность изложения материала семинаристом',
                4,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Насколько полученные знания пригодятся на практике?',
                5,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                null,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Вы бы пошли на предмет, если бы он был необязательным?',
                6,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                null,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Посоветуете ли вы этот предмет студентам младших курсов?',
                7,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                null,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Comment->value,
                'Чем запомнился лектор?',
                8,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null, 255),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Comment->value,
                'Чем запомнился семинарист?',
                9,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null, 255),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Comment->value,
                'Чего не хватает в предмете?',
                10,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null, 255),
                null,
            ),
        ];

        $subjects = $this
            ->subjectsUseCase
            ->execute(new GetAllSubjectsDto());

        $this->io->info(
            sprintf(
                'Найдено %d предметов',
                $subjects->getTotal(),
            ),
        );

        $dtos = new ProjectionAwareDataProvider(
            $subjects,
            function (Subject $subject) use (&$createItemDtos): CreateSurveyDto {
                return new CreateSurveyDto(
                    sprintf('Выскажи свое мнение о курсе'),
                    $subject->getId()->toRfc4122(),
                    (new DateTimeImmutable())->modify('+1 month')->format(DATE_RFC3339),
                    $createItemDtos,
                );
            },
        );

        $this->io->info('Создаем опросы...');
        $surveys = $this
            ->createSurveysUseCase
            ->execute(iterator_to_array($dtos->getItems()));
        $this->io->success(
            sprintf(
                "Создано %d опросов для предметов\n%s",
                count($surveys),
                implode(
                    "\n",
                    array_map(
                        fn(Survey $s) => $s->getSubject()->getName(),
                        $surveys,
                    ),
                ),
            ),
        );
        return self::SUCCESS;
    }
}
