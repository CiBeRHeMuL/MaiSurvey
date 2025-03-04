<?php

namespace App\Infrastructure\Service\SurveyStat;

use Psr\Log\LoggerInterface;

/**
 * Генератор сжатой выборки из комментариев
 */
interface CommentsSummaryGeneratorInterface
{
    /**
     * @param string[] $comments
     *
     * @return string
     */
    public function generateSummary(array $comments): string;

    public function setLogger(LoggerInterface $logger): static;
}
