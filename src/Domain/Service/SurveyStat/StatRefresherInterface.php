<?php

namespace App\Domain\Service\SurveyStat;

use App\Domain\Entity\Survey;
use Psr\Log\LoggerInterface;

interface StatRefresherInterface
{
    public function getLogger(): LoggerInterface;

    public function setLogger(LoggerInterface $logger): static;

    public function refreshStat(Survey $survey): void;
}
