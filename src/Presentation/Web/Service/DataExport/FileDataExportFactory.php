<?php

namespace App\Presentation\Web\Service\DataExport;

use Psr\Log\LoggerInterface;
use RuntimeException;

class FileDataExportFactory implements FileDataExportFactoryInterface
{
    protected LoggerInterface $logger;

    public function get(string $id): FileDataExportInterface
    {
        $dataExport = match ($id) {
            'xlsx' => new XlsxDataExport(),
            'csv' => new CsvDataExport(),
            default => throw new RuntimeException('Неизвестный id'),
        };
        $dataExport->setLogger($this->getLogger());
        return $dataExport;
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
