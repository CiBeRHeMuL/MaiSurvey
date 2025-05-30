<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Survey\GetSurveysDto;
use App\Application\UseCase\SurveyStat\GetSurveysStatUseCase;
use App\Application\UseCase\SurveyStat\GetSurveyStatByIdUseCase;
use App\Domain\Dto\SurveyStat\StatNCUser;
use App\Domain\Dto\SurveyStatItem\ChoiceStatData;
use App\Domain\Dto\SurveyStatItem\CommentStatData;
use App\Domain\Dto\SurveyStatItem\MultiChoiceStatData;
use App\Domain\Dto\SurveyStatItem\RatingStatData;
use App\Domain\Entity\SurveyStat as DomainSurveyStat;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Domain\Helper\HArray;
use App\Domain\Helper\HString;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\SurveyStat;
use App\Presentation\Web\Response\Response;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalColorScale;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Writer\Word2007;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;
use Throwable;

class SurveyStatController extends BaseController
{
    #[Route('/surveys/{id}/stat', 'get-survey-stat-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyStatView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-stats')]
    #[LOA\SuccessResponse(SurveyStat::class)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ErrorResponse(500)]
    public function getById(
        Uuid $id,
        GetSurveyStatByIdUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $stat = $useCase->execute($id);
        if ($stat === null) {
            return Response::notFound();
        } else {
            return Response::success(
                new SuccessResponse(
                    SurveyStat::fromStat($stat),
                ),
            );
        }
    }

    /** Выгрузить статистику */
    #[Route('/surveys/{id}/stat/export/xlsx', 'export-survey-stat-by-id', requirements: ['id' => Requirement::UUID], methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyStatView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-stats')]
    #[LOA\FileResponse(['docx'])]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ErrorResponse(500)]
    public function exportById(
        Uuid $id,
        GetSurveyStatByIdUseCase $useCase,
        LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ): JsonResponse|BinaryFileResponse {
        $useCase->setLogger($logger);
        $stat = $useCase->execute($id);
        if ($stat === null) {
            return Response::notFound();
        }
        try {
            $word = $this->generateWordStat($stat);
            $writer = new Word2007($word);

            $exportType = 'docx';

            // Сохраняем
            if (!is_dir("$projectDir/export/$exportType")) {
                mkdir("$projectDir/export/$exportType", 0777, true);
            }
            $exportFileName = HString::rusToEng(
                    str_replace(
                        '/',
                        '-',
                        $stat->getSurvey()->getSubject()->getName()
                        . ' ' . $stat->getSurvey()->getSubject()->getSemester()->getName(),
                    ),
                )
                . '_'
                . (new DateTimeImmutable())->format('Y-m-d H:i:s')
                . ".$exportType";
            $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
            $writer->save($fullExportFileName);

            return $this->file($fullExportFileName, str_replace(':', '.', HString::rusToEng($exportFileName)))
                ->deleteFileAfterSend();
        } catch (Throwable $e) {
            $logger->error($e);
            return Response::error(
                new ErrorResponse(
                    new Error(
                        ErrorSlugEnum::InternalServerError->getSlug(),
                        'Не удалось отправить файл',
                    ),
                ),
                HttpStatusCodeEnum::InternalServerError,
            );
        }
    }

    /** Выгрузить статистику по нескольким опросам */
    #[Route('/surveys/stat/export/xlsx', 'export-survey-stat-all', methods: ['GET'])]
    #[IsGranted(PermissionEnum::SurveyStatView->value, statusCode: 404, exceptionCode: 404)]
    #[OA\Tag('survey-stats')]
    #[LOA\FileResponse(['xlsx'])]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ErrorResponse(500)]
    public function exportAll(
        GetSurveysStatUseCase $useCase,
        LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
        #[MapQueryString(
            serializationContext: [AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [GetSurveysDto::class => ['limit' => null]]],
            validationFailedStatusCode: 422,
        )]
        GetSurveysDto $dto = new GetSurveysDto(limit: null),
    ): BinaryFileResponse {
        $useCase->setLogger($logger);
        $statProvider = $useCase->execute($dto);
        $exportType = 'xlsx';
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);
        $spreadsheet->removeSheetByIndex(0);

        $topBorderStyle = [
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];
        $bottomBorderStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];

        $commonWorksheet = new Worksheet($spreadsheet, 'Общая информация');
        $spreadsheet->addSheet($commonWorksheet);
        $row = 1;
        $commonWorksheet->setCellValue("A$row", 'Семестр');
        $commonWorksheet->setCellValue("B$row", 'Группа');
        $commonWorksheet->setCellValue("C$row", 'Предмет');
        $commonWorksheet->setCellValue("D$row", 'Всего');
        $commonWorksheet->setCellValue("E$row", 'Проголосовало');
        $commonWorksheet->setCellValue("F$row", 'Процент проголосовавших');
        $row++;

        foreach ($statProvider->getItems() as $stat) {
            $commonWorksheet->getStyle("A$row")->applyFromArray($topBorderStyle);
            $commonWorksheet->getStyle("B$row")->applyFromArray($topBorderStyle);
            $commonWorksheet->getStyle("C$row")->applyFromArray($topBorderStyle);
            $commonWorksheet->getStyle("D$row")->applyFromArray($topBorderStyle);
            $commonWorksheet->getStyle("E$row")->applyFromArray($topBorderStyle);
            $commonWorksheet->getStyle("F$row")->applyFromArray($topBorderStyle);
            foreach ($stat->getCountsByGroups() as $group) {
                $commonWorksheet->setCellValue("A$row", $stat->getSurvey()->getSubject()->getSemester()->getName());
                $commonWorksheet->setCellValue("B$row", $group->getName());
                $commonWorksheet->setCellValue("C$row", $stat->getSurvey()->getSubject()->getName());
                $commonWorksheet->setCellValue("D$row", (string)$group->getAvailableCount());
                $commonWorksheet->setCellValue("E$row", (string)$group->getCompletedCount());
                $commonWorksheet->setCellValue(
                    "F$row",
                    ($group->getCompletedCount() / $group->getAvailableCount() * 100) . '%',
                    new AdvancedValueBinder(),
                );
                $row++;
            }
            $commonWorksheet->getStyle('A' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $commonWorksheet->getStyle('B' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $commonWorksheet->getStyle('C' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $commonWorksheet->getStyle('D' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $commonWorksheet->getStyle('E' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $commonWorksheet->getStyle('F' . ($row - 1))->applyFromArray($bottomBorderStyle);
            $this->generateStatWorksheet($stat, $spreadsheet);
        }

        $conditionalColorScale = new ConditionalColorScale();
        $conditionalColorScale->setMinimumColor(new Color('FFF8696B'))
            ->setMidpointColor(new Color('FFFFEB84'))
            ->setMaximumColor(new Color('FF63BE7B'))
            ->setMinimumConditionalFormatValueObject(new ConditionalFormatValueObject('min'))
            ->setMidpointConditionalFormatValueObject(new ConditionalFormatValueObject('percentile', '50'))
            ->setMaximumConditionalFormatValueObject(new ConditionalFormatValueObject('max'));
        $commonWorksheet->setConditionalStyles(
            'F1:F1048576',
            [(new Conditional())->setColorScale($conditionalColorScale)->setConditionType(Conditional::CONDITION_COLORSCALE)]
        );
        foreach ($commonWorksheet->getColumnIterator() as $column) {
            $commonWorksheet->getColumnDimension($column->getColumnIndex())
                ->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        // Сохраняем
        if (!is_dir("$projectDir/export/$exportType")) {
            mkdir("$projectDir/export/$exportType", 0777, true);
        }
        $exportFileName = 'survey_stat_all_'
            . (new DateTimeImmutable())->format('Y-m-d H:i:s')
            . ".$exportType";
        $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
        $writer->save($fullExportFileName);

        return $this->file($fullExportFileName, str_replace(':', '.', HString::rusToEng($exportFileName)))
            ->deleteFileAfterSend();
    }

    /**
     * @param DomainSurveyStat $stat
     * @param Spreadsheet $spreadsheet
     * @return Worksheet
     */
    private function generateStatWorksheet(DomainSurveyStat $stat, Spreadsheet $spreadsheet): Worksheet
    {
        $title = $stat->getSurvey()->getSubject()->getSemester()->getShortName()
            . ' – ' . $stat->getSurvey()->getSubject()->getName();
        $title = mb_strlen($title) > 25
            ? mb_substr($title, 0, 25) . '...'
            : $title;
        $title = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $title);
        $worksheet = new Worksheet($spreadsheet, $title);
        $spreadsheet->addSheet($worksheet, retitleIfNeeded: true);

        $worksheet->setCellValue(
            'A1',
            "Статистика по опросу по предмету \"{$stat->getSurvey()->getSubject()->getName()}\" за {$stat->getSurvey()->getSubject()->getSemester()->getName()}",
        );
        $worksheet->mergeCells('A1:G1');

        $worksheet->setCellValue('A2', 'Вопрос');
        $worksheet->getStyle('A2')
            ->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_YELLOW],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        $worksheet->setCellValue('B2', 'Преподаватель');
        $worksheet->getStyle('B2')
            ->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_YELLOW],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

        $row = 3;
        foreach ($stat->getItems() as $item) {
            $worksheet->setCellValue(
                "A$row",
                $item->getItem()->getText(),
            );
            $worksheet->setCellValue(
                "C$row",
                "Ответило {$item->getCompletedCount()} / {$item->getAvailableCount()}",
            );
            $row += 2;

            foreach ($item->getStats() as $itemStat) {
                $worksheet->setCellValue(
                    "B$row",
                    $itemStat->getTeacherName() ?? 'Общая статистика',
                );
                $worksheet->getStyle("B$row")
                    ->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['argb' => Color::COLOR_GREEN],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                $worksheet->setCellValue(
                    "C$row",
                    "Ответило {$itemStat->getCompletedCount()} / {$itemStat->getAvailableCount()}",
                );
                $row++;

                switch ($itemStat->getType()) {
                    case SurveyItemTypeEnum::Rating:
                        /** @var RatingStatData $itemStat */
                        $worksheet->setCellValue("B$row", 'Рейтинг');
                        $worksheet->setCellValue('B' . ($row + 1), 'Количество');
                        $worksheet->setCellValue('B' . ($row + 2), 'Среднее');
                        $worksheet->setCellValue('C' . ($row + 2), round($itemStat->getAverage(), 2));
                        $worksheet->getStyle('C' . ($row + 2))
                            ->applyFromArray(['numberFormat' => ['formatCode' => NumberFormat::FORMAT_NUMBER_00]]);
                        $column = 'C';
                        foreach ($itemStat->getCounts() as $count) {
                            $worksheet->setCellValue("$column$row", $count->getRating());
                            $worksheet->setCellValue($column . ($row + 1), $count->getCount());
                            $column++;
                        }

                        $row += 3;
                        break;
                    case SurveyItemTypeEnum::Choice:
                    case SurveyItemTypeEnum::MultiChoice:
                        /** @var ChoiceStatData|MultiChoiceStatData $itemStat */
                        $worksheet->setCellValue("B$row", 'Выбор');
                        $worksheet->setCellValue('B' . ($row + 1), 'Количество');
                        $column = 'C';
                        foreach ($itemStat->getCounts() as $count) {
                            $worksheet->setCellValue("$column$row", $count->getChoice());
                            $worksheet->setCellValue($column . ($row + 1), $count->getCount());
                            $column++;
                        }

                        $row += 2;
                        break;
                    case SurveyItemTypeEnum::Comment:
                        /** @var CommentStatData $itemStat */
                        $worksheet->setCellValue("B$row", 'Комментарии');
                        $worksheet->getStyle("B$row")
                            ->applyFromArray([
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                    'vertical' => Alignment::VERTICAL_TOP,
                                ],
                            ]);
                        $worksheet->setCellValue("C$row", implode("\n", $itemStat->getComments()));
                        $worksheet->getStyle("C$row")->setQuotePrefix(true);
                        $row += 1;
                        break;
                }
                $row++;
            }
        }

        $worksheet->setCellValue('J2', 'Пользователи не прошедшие опрос');
        $worksheet->getStyle('J2')
            ->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_YELLOW],
                ],
            ]);
        $worksheet->mergeCells('J2:K2');
        $worksheet->setCellValue('J3', 'Группа');
        $worksheet->getStyle('J3')
            ->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_GREEN],
                ],
            ]);
        $worksheet->setCellValue('K3', 'Студент');
        $worksheet->getStyle('K3')
            ->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => Color::COLOR_GREEN],
                ],
            ]);

        $topBorderStyle = [
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];
        $bottomBorderStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => Color::COLOR_BLACK],
                ],
            ],
        ];

        $row = 4;
        $lastGroup = null;
        foreach ($stat->getNotCompletedUsers() as $notCompletedUser) {
            $worksheet->setCellValue("J$row", $notCompletedUser->getGroup());
            $worksheet->setCellValue("K$row", $notCompletedUser->getName());

            if ($notCompletedUser->getGroup() !== $lastGroup) {
                $lastGroup = $notCompletedUser->getGroup();
                $worksheet->getStyle("J$row")
                    ->applyFromArray($topBorderStyle);
                $worksheet->getStyle('J' . ($row - 1))
                    ->applyFromArray($bottomBorderStyle);
                $worksheet->getStyle("K$row")
                    ->applyFromArray($topBorderStyle);
                $worksheet->getStyle('K' . ($row - 1))
                    ->applyFromArray($bottomBorderStyle);
            }

            $row++;
        }

        foreach ($worksheet->getColumnIterator() as $column) {
            $worksheet
                ->getColumnDimension($column->getColumnIndex())
                ->setAutoSize(true);
        }
        $worksheet->calculateColumnWidths();
        $worksheet
            ->getColumnDimension('C')
            ->setAutoSize(false)
            ->setWidth(20);
        return $worksheet;
    }

    private function generateWordStat(DomainSurveyStat $stat): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $standardIndent = 709; // Стандартный отступ в 1,25 см в двадцатых долях пункта

        $section = $phpWord->addSection();
        $section->addFooter()->addPreserveText('{ PAGE }', [], ['alignment' => 'center']);

        $section->addText(
            "Статистика по опросу по предмету \"{$stat->getSurvey()->getSubject()->getName()}\""
            . " за {$stat->getSurvey()->getSubject()->getSemester()->getName()}",
            ['bold' => true, 'size' => 18],
            ['alignment' => 'center'],
        );
        $section->addText(
            msgfmt_format_message(
                'ru-RU',
                '{c,plural,one{Прошел # студент} few{Прошло # студентов} many{Прошли # студентов} other{Прошло # студентов}} из {a}',
                ['c' => $stat->getCompletedCount(), 'a' => $stat->getAvailableCount()],
            ),
        );
        $section->addText(
            sprintf(
                'Процент прохождения: %.2f%%',
                $stat->getAvailableCount()
                    ? $stat->getCompletedCount() / $stat->getAvailableCount() * 100
                    : 0.00,
            ),
        );

        $section->addText(
            "Группы, которым доступен опрос:",
            ['bold' => true, 'size' => 16],
            ['alignment' => 'left'],
        );
        foreach ($stat->getAvailableGroups() as $group) {
            $section->addListItem($group, 0);
        }

        $section->addText(
            "Ответы:",
            ['bold' => true, 'size' => 16],
            ['alignment' => 'left'],
        );

        foreach ($stat->getItems() as $item) {
            $section->addListItem($item->getItem()->getText(), 0, ['bold' => true, 'size' => 14]);
            $section->addListItem(
                msgfmt_format_message(
                    'ru-RU',
                    '{c,plural,one{Прошел # студент} few{Прошло # студентов} many{Прошли # студентов} other{Прошло # студентов}} из {a}',
                    ['c' => $item->getCompletedCount(), 'a' => $item->getAvailableCount()],
                ),
                1,
            );
            $section->addListItem(
                sprintf(
                    'Процент прохождения: %.2f%%',
                    $item->getAvailableCount()
                        ? $item->getCompletedCount() / $item->getAvailableCount() * 100
                        : 0.00,
                ),
                1,
            );

            foreach ($item->getStats() as $itemStat) {
                $section->addListItem(
                    $itemStat->getTeacherName() ?? 'Общая статистика',
                    1,
                    ['bold' => true, 'size' => 14],
                );
                $section->addListItem('Статистика', 2);
                $section->addListItem(
                    msgfmt_format_message(
                        'ru-RU',
                        '{c,plural,one{Прошел # студент} few{Прошло # студента} many{Прошли # студентов} other{Прошло # студентов}} из {a}',
                        ['c' => $itemStat->getCompletedCount(), 'a' => $itemStat->getAvailableCount()],
                    ),
                    3,
                );
                $section->addListItem(
                    sprintf(
                        'Процент прохождения: %.2f%%',
                        $itemStat->getAvailableCount()
                            ? $itemStat->getCompletedCount() / $itemStat->getAvailableCount() * 100
                            : 0.00,
                    ),
                    3,
                );
                $section->addListItem('Ответы', 2);

                switch ($itemStat->getType()) {
                    case SurveyItemTypeEnum::Rating:
                        /** @var RatingStatData $itemStat */
                        $section->addListItem(sprintf('Средний рейтинг: %.2f', $itemStat->getAverage()), 3);
                        $table = $section->addTable([
                            'borderSize' => 1,
                            'borderColor' => 'black',
                            'cellMarginLeft' => 114,
                            'cellMarginRight' => 114,
                            'indent' => new \PhpOffice\PhpWord\ComplexType\TblWidth($standardIndent * 4, TblWidth::TWIP),
                        ]);

                        $ratingRow = $table->addRow();
                        $ratingRow->addCell()->addText('Рейтинг');
                        $countRow = $table->addRow();
                        $countRow->addCell()->addText('Количество');

                        foreach ($itemStat->getCounts() as $count) {
                            $ratingRow->addCell()->addText($count->getRating());
                            $countRow->addCell()->addText($count->getCount());
                        }

                        break;
                    case SurveyItemTypeEnum::Choice:
                    case SurveyItemTypeEnum::MultiChoice:
                        /** @var ChoiceStatData|MultiChoiceStatData $itemStat */
                        $table = $section->addTable([
                            'borderSize' => 1,
                            'borderColor' => 'black',
                            'cellMarginLeft' => 114,
                            'cellMarginRight' => 114,
                            'indent' => new \PhpOffice\PhpWord\ComplexType\TblWidth($standardIndent * 4, TblWidth::TWIP),
                        ]);

                        $choiceRow = $table->addRow();
                        $choiceRow->addCell()->addText('Рейтинг');
                        $countRow = $table->addRow();
                        $countRow->addCell()->addText('Количество');

                        foreach ($itemStat->getCounts() as $count) {
                            $choiceRow->addCell()->addText($count->getChoice());
                            $countRow->addCell()->addText($count->getCount());
                        }
                        break;
                    case SurveyItemTypeEnum::Comment:
                        /** @var CommentStatData $itemStat */
                        foreach ($itemStat->getComments() as $comment) {
                            $section->addListItem($comment, 3);
                        }
                        break;
                }
            }
        }

        $section->addText(
            "Студенты, не прошедшие опрос:",
            ['bold' => true, 'size' => 16],
            ['alignment' => 'left'],
        );

        /** @var array<string, string[]> $notCompletedStudents */
        $notCompletedStudents = HArray::groupExtended(
            $stat->getNotCompletedUsers(),
            fn(StatNCUser $u) => $u->getGroup(),
            projection: fn(StatNCUser $u): string => $u->getName(),
        );

        foreach ($notCompletedStudents as $group => $groupNotCompletedStudent) {
            $section->addListItem($group, 0);
            foreach ($groupNotCompletedStudent as $student) {
                $section->addListItem($student, 1);
            }
        }

        return $phpWord;
    }
}
