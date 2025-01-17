<?php

namespace App\Domain\Service\FileReader;

use InvalidArgumentException;
use Iterator;
use RuntimeException;

interface FileReaderInterface
{
    /**
     * Открывает файл для чтения
     *
     * @param string $fileName
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function openFile(string $fileName): static;

    /**
     * Возвращает строку из файла excel в виде ассоциативного массива ['A' => '1', 'B' => 'adf']
     *
     * @param int $startIndex
     * @param int|null $endIndex
     *
     * @return Iterator<string, string[]>
     * @throws RuntimeException
     */
    public function getRows(int $startIndex = 1, int|null $endIndex = null): Iterator;

    /**
     * Возвращает номер последней строки файла
     * @return int
     */
    public function getHighestRow(): int;
}
