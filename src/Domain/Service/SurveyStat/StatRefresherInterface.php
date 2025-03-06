<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\Survey;
use Psr\Log\LoggerInterface;

interface StatRefresherInterface
{
    public function getLogger(): LoggerInterface;

    public function setLogger(LoggerInterface $logger): static;

    /**
     * @param Survey[]|null $surveys
     * @param bool $force
     *
     * @return void
     */
    public function refreshStats(array|null $surveys = null, bool $force = false): void;
}
