<?php

namespace App\Presentation\Web\Response\Model\Common;

use App\Domain\DataProvider\DataProviderInterface;
use Closure;

/**
 * Поле data для успешного ответа с пагинацией
 */
readonly class PaginatedData
{
    /**
     * @param array $items
     * @param int|null $offset
     * @param int|null $limit
     * @param int|null $count
     * @param string|null $sort_by
     * @param string|null $sort_type
     */
    public function __construct(
        public array $items = [],
        public int|null $offset = null,
        public int|null $limit = null,
        public int|null $count = null,
        public string|null $sort_by = null,
        public string|null $sort_type = null,
    ) {
    }

    public static function fromDataProvider(
        DataProviderInterface $dataProvider,
        Closure|null $itemsProjection = null,
    ): self {
        $itemsProjection ??= fn($el) => $el;
        $items = [];
        foreach ($dataProvider->getItems() as $item) {
            $items[] = $itemsProjection($item);
        }
        $sortColumn = $dataProvider->getDataSort()?->getSortColumns()[0];
        return new self(
            $items,
            $dataProvider->getDataLimit()->getOffset(),
            $dataProvider->getDataLimit()->getLimit(),
            $dataProvider->getTotal(),
            $sortColumn?->getColumn(),
            $sortColumn !== null
                ? $sortColumn->getSort() === SORT_ASC ? 'asc' : 'desc'
                : null,
        );
    }
}
