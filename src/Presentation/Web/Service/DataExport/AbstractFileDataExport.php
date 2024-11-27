<?php

namespace App\Presentation\Web\Service\DataExport;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class AbstractFileDataExport implements FileDataExportInterface
{
    /**
     * @var resource
     */
    private $file;
    private LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @inheritDoc
     */
    public function setFile($file): static
    {
        if (!is_resource($file) && !is_string($file)) {
            throw new InvalidArgumentException('Ожидается поток или имя файла');
        }
        if (is_string($file)) {
            $this->file = fopen($file, 'w');
        } else {
            $this->file = $file;
        }
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }
}
