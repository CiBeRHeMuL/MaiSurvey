<?php

namespace App\Presentation\Web\Service\DataExport;

use Psr\Log\LoggerInterface;

interface DataExportInterface
{
    /**
     * Экспортировать данные в хранилище
     *
     * @param array $data
     *
     * @return bool
     */
    public function exportArray(array $data): bool;

    public function setLogger(LoggerInterface $logger): static;

    public function getLogger(): LoggerInterface;
}
