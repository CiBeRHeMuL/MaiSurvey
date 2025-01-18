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
                'Увлекательность материала и его подача на лекциях',
                1,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Увлекательность материала и его подача на пз',
                2,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Использование примеров на лекциях',
                3,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Использование примеров на пз',
                4,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Понятность изложения на лекциях',
                5,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Понятность изложения на пз',
                6,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Доброжелательность преподавателя на лекциях',
                7,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Доброжелательность преподавателя на пз',
                8,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените свои остаточные знания по предмету на лекциях',
                9,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Оцените свои остаточные знания по предмету на пз',
                10,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'На сколько, на твой взгляд, полученные на лекциях знания пригодятся на практике',
                11,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'На сколько, на твой взгляд, полученные на пз знания пригодятся на практике',
                12,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Ты пошел бы на лекции по этому предмету, если бы они были необязательными (1 - не пошел, 5 - с удовольствием пошел бы)',
                13,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Rating->value,
                'Ты пошел бы на пз по этому предмету, если бы они были необязательными (1 - не пошел, 5 - с удовольствием пошел бы)',
                14,
                new RatingItemData(SurveyItemTypeEnum::Rating->value, 1, 5),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                false,
                SurveyItemTypeEnum::Comment->value,
                'Чем запомнился лектор?',
                15,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Comment->value,
                'Чем запомнился семинарист?',
                16,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null),
                TeacherSubjectTypeEnum::PracticalLesson->value,
            ),
            new CreateItemDto(
                false,
                SurveyItemTypeEnum::Comment->value,
                'Чего, на твой взгляд, не хватает на лекциях?',
                17,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null),
                TeacherSubjectTypeEnum::Lecture->value,
            ),
            new CreateItemDto(
                true,
                SurveyItemTypeEnum::Comment->value,
                'Чего, на твой взгляд, не хватает на пз?',
                18,
                new CommentItemData(SurveyItemTypeEnum::Comment->value, null),
                TeacherSubjectTypeEnum::PracticalLesson->value,
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
                    sprintf('Опрос по предмету "%s"', $subject->getName()),
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
                'Создано %d опросов',
                count($surveys),
            ),
        );
        return self::SUCCESS;
    }
}
