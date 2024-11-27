<?php

namespace App\Presentation\Web\Service\DataExport;

use Psr\Log\LoggerInterface;
use RuntimeException;

interface DataExportFactoryInterface
{
    public function setLogger(LoggerInterface $logger): static;

    public function getLogger(): LoggerInterface;

    /**
     * @param string $id
     *
     * @return DataExportInterface
     * @throws RuntimeException
     */
    public function get(string $id): DataExportInterface;
}
