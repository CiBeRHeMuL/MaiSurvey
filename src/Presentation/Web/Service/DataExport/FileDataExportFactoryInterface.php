<?php

namespace App\Presentation\Web\Service\DataExport;

interface FileDataExportFactoryInterface extends DataExportFactoryInterface
{
    public function get(string $id): FileDataExportInterface;
}
