<?php

namespace App\Domain\Helper;

use Random\Randomizer;

class HString
{
    /**
     * Generate a random string.
     * If $length is less than 1, it will be set to 1.
     *
     * @param int $length
     *
     * @return string
     */
    public static function random(int $length = 10): string
    {
        $length = max(1, $length);
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomizer = new Randomizer();
        return $randomizer->getBytesFromString($chars, $length);
    }
}
