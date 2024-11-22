<?php

namespace App\Presentation\Web\Enum;

use App\Domain\Enum\SortTypeEnum as DomainSortTypeEnum;
use InvalidArgumentException;

enum SortTypeEnum: string
{
    case Asc = 'asc';
    case Desc = 'desc';

    public function getSort(): int
    {
        return match ($this) {
            self::Asc => SORT_ASC,
            self::Desc => SORT_DESC,
        };
    }

    public static function fromType(int $type): self
    {
        return match ($type) {
            SORT_ASC => self::Asc,
            SORT_DESC => self::Desc,
            default => throw new InvalidArgumentException('Invalid sort type'),
        };
    }

    public static function fromDomainSort(DomainSortTypeEnum $type): self
    {
        return match ($type) {
            DomainSortTypeEnum::Asc => self::Asc,
            DomainSortTypeEnum::Desc => self::Desc,
            default => throw new InvalidArgumentException('Invalid sort type'),
        };
    }

    public function toDomainType(): DomainSortTypeEnum
    {
        return match ($this) {
            self::Asc => DomainSortTypeEnum::Asc,
            self::Desc => DomainSortTypeEnum::Desc,
            default => throw new InvalidArgumentException('Invalid sort type'),
        };
    }

    public function getName(): string
    {
        return $this->value;
    }
}
