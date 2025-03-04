<?php

namespace App\Infrastructure\Service\SurveyStat;

use Psr\Log\LoggerInterface;

class GptCommentsSummaryGenerator implements CommentsSummaryGeneratorInterface
{
    private LoggerInterface $logger;

    /**
     * @inheritDoc
     */
    public function generateSummary(array $comments): string
    {
        return 'Краткая выдержка из всех комментариев сгенерированная GPT';
    }

    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }
}
