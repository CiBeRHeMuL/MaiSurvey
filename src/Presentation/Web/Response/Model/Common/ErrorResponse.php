<?php

namespace App\Presentation\Web\Response\Model\Common;

/**
 * Ответ с ошибкой
 */
readonly class ErrorResponse
{
    public function __construct(
        public Error $error,
    ) {
    }
}
