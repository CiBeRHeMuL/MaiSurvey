<?php

namespace App\Infrastructure\Service\FileReader;

use App\Domain\Service\FileReader\FileReaderInterface;
use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsxFileReader implements FileReaderInterface
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
     * @param bool $allowEmptyRows
     *
     * @return Iterator<string, string[]>
     */
    public function getRows(int $startIndex = 1, int|null $endIndex = null, bool $allowEmptyRows = false): Iterator
    {
        $endIndex ??= $this->worksheet->getHighestRow();
        if ($endIndex < $startIndex) {
            return new ArrayIterator([]);
        }
        foreach ($this->worksheet->getRowIterator($startIndex, $endIndex) as $k => $row) {
            $empty = $allowEmptyRows === false
                && $row->isEmpty(
                    CellIterator::TREAT_NULL_VALUE_AS_EMPTY_CELL | CellIterator::TREAT_EMPTY_STRING_AS_EMPTY_CELL,
                );
            if ($empty) {
                continue;
            }
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
