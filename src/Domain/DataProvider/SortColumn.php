<?php

namespace App\Domain\DataProvider;

/**
 * Простейшая реализация SortColumnInterface.
 */
class SortColumn implements SortColumnInterface
{
    public function __construct(
        private string $column,
        private int $sort,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * @inheritDoc
     */
    public function getSort(): int
    {
        return $this->sort;
    }
}
