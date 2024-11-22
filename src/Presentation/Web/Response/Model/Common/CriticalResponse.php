<?php

namespace App\Presentation\Web\Response\Model\Common;

use Throwable;

/**
 * Используется только в dev среде!
 */
readonly class CriticalResponse
{
    public ExceptionModel $exception;

    public function __construct(Throwable $e)
    {
        $this->exception = ExceptionModel::fromThrowable($e);
    }
}
