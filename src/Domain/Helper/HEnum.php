<?php

namespace App\Domain\Helper;

use BackedEnum;
use Closure;
use UnitEnum;

class HEnum
{
    /**
     * @template T of UnitEnum
     * @param class-string<T> $enum
     *
     * @return (T is BackedEnum ? (callable(): array<value-of<T>>) : (callable(): array<key-of<T>>)
     */
    public static function choices(string $enum): Closure
    {
        return function () use ($enum) {
            return is_subclass_of($enum, BackedEnum::class, true)
                ? array_map(fn(BackedEnum $e) => $e->value, $enum::cases())
                : array_map(fn(UnitEnum $e) => $e->name, $enum::cases());
        };
    }
}
