<?php

namespace App\Presentation\Web\Service\DataExport;

use InvalidArgumentException;

interface FileDataExportInterface extends DataExportInterface
{
    /**
     * @param resource|string $file
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function setFile($file): static;

    /**
     * @return resource
     */
    public function getFile();
}
