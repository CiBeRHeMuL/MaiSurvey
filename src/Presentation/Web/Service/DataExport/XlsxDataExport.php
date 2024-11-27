<?php

namespace App\Presentation\Web\Service\DataExport;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

/**
 * Экспорт данных в xlsx файл
 */
class XlsxDataExport extends AbstractFileDataExport
{
    /**
     * @inheritDoc
     */
    public function exportArray(array $data): bool
    {
        try {
            $spreadsheet = new Spreadsheet();
            $writer = new Xlsx($spreadsheet);
            $worksheet = $spreadsheet->getSheet(0);

            [$startColumn, $startRow] = Coordinate::coordinateFromString('A1');
            foreach ($data as $row) {
                $currentColumn = $startColumn;
                foreach ($row as $cell) {
                    if ($cell !== null) {
                        $worksheet
                            ->getCell($currentColumn . $startRow)
                            ->setValueExplicit($cell);
                    }
                    ++$currentColumn;
                }
                ++$startRow;
            }
            foreach ($worksheet->getColumnIterator() as $column) {
                $worksheet
                    ->getColumnDimension($column->getColumnIndex())
                    ->setAutoSize(true);
            }
            $worksheet->calculateColumnWidths();
            $writer->save($this->getFile());
            return true;
        } catch (Throwable $e) {
            $this->getLogger()->error($e);
            return false;
        }
    }
}
