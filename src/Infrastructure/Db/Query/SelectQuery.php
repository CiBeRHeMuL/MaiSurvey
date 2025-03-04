<?php

namespace App\Infrastructure\Db\Query;

use Qstart\Db\QueryBuilder\DML\Query\SelectQuery as BaseSelectQuery;

class SelectQuery extends BaseSelectQuery
{
    /**
     * @var array<string, BaseSelectQuery>|null $with
     */
    protected array|null $with = null;

    protected function addWith(string $alias, BaseSelectQuery $query): static
    {
        $this->with ??= [];
        $this->with[$alias] = $query;
        return $this;
    }

    /**
     * @param array<string, BaseSelectQuery>|null $with
     *
     * @return $this
     */
    protected function with(array|null $with = null): static
    {
        $this->with = $with;
        return $this;
    }

    public function getWith(): array|null
    {
        return $this->with;
    }
}
