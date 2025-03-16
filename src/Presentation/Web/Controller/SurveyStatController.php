<?php

namespace App\Presentation\Web\Controller;

use App\Application\UseCase\SurveyStat\GetSurveyStatByIdUseCase;
use App\Domain\Dto\SurveyStatItem\ChoiceStatData;
use App\Domain\Dto\SurveyStatItem\CommentStatData;
use App\Domain\Dto\SurveyStatItem\MultiChoiceStatData;
use App\Domain\Dto\SurveyStatItem\RatingStatData;
use App\Domain\Enum\PermissionEnum;
use App\Domain\Enum\SurveyItemTypeEnum;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\SurveyStat;
use App\Presentation\Web\Response\Response;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    #[LOA\FileResponse(['xlsx'])]
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
            $exportType = 'xlsx';
            $spreadsheet = new Spreadsheet();
            $writer = new Xlsx($spreadsheet);
            $worksheet = $spreadsheet->getSheet(0);
            [$startColumn, $startRow] = Coordinate::coordinateFromString('A1');
            $worksheet->setCellValue(
                'A1',
                "Статистика по опросу по предмету \"{$stat->getSurvey()->getSubject()->getName()}\"",
            );
            $worksheet->mergeCells('A1:G1');

            $worksheet->setCellValue('A2', 'Вопрос');
            $worksheet
                ->getStyle('A2')
                ->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFFF00'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            $worksheet->setCellValue('B2', 'Преподаватель');
            $worksheet
                ->getStyle('B2')
                ->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFFF00'],
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
                    $worksheet
                        ->getStyle("B$row")
                        ->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => '00FF00'],
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
                            $worksheet
                                ->getStyle('C' . ($row + 2))
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
                            $worksheet->setCellValue("B$row", 'Сводный комментарий');
                            $worksheet->setCellValue("C$row", $itemStat->getSummary());
                            break;
                    }
                    $row++;
                }
            }

            foreach ($worksheet->getColumnIterator() as $column) {
                $worksheet
                    ->getColumnDimension($column->getColumnIndex())
                    ->setAutoSize(true);
            }
            $worksheet->calculateColumnWidths();

            // Сохраняем
            if (!is_dir("$projectDir/export/$exportType")) {
                mkdir("$projectDir/export/$exportType", 0777, true);
            }
            $exportFileName = "survey_stat_{$id->toRfc4122()}_" . (string)time() . ".$exportType";
            $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
            $writer->save($fullExportFileName);

            return $this->file($fullExportFileName, $exportFileName);
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
}
