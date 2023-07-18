<?php

namespace Stu\Module\Control;

/**
 * This class adds the possibility to inject a random generator
 */
class StuRandom
{
    public function rand(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    public function array_rand(array $array): string|int
    {
        return array_rand($array);
    }
}
