<?php

namespace App\Infrastructure\Service\DataImport;

use App\Domain\Service\DataImport\DataImportInterface;
use InvalidArgumentException;
use Iterator;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsxDataImport implements DataImportInterface
{
    private Spreadsheet $spreadsheet;
    private Worksheet $worksheet;
    private BaseReader $reader;

    public function openFile(string $fileName): static
    {
        $xlsxReader = new Xlsx();
        if ($xlsxReader->canRead($fileName)) {
            $this->reader = $xlsxReader;
        } else {
            throw new InvalidArgumentException('Cannot read file. Invalid file content');
        }
        $this->spreadsheet = $this->reader->load($fileName);
        $this->worksheet = $this->spreadsheet->getSheet(0);
        return $this;
    }

    /**
     * @param int $startIndex
     * @param int|null $endIndex
     *
     * @return Iterator<string, string[]>
     */
    public function getRows(int $startIndex = 1, int|null $endIndex = null): Iterator
    {
        foreach ($this->worksheet->getRowIterator($startIndex, $endIndex) as $k => $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cKey => $cell) {
                $rowData[$cKey] = $cell->getFormattedValue();
            }
            yield $k => $rowData;
        }
    }

    public function getHighestRow(): int
    {
        return $this->worksheet->getHighestRow();
    }
}
