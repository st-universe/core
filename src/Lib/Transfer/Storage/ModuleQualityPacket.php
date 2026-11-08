<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Storage;

use InvalidArgumentException;

final class ModuleQualityPacket
{
    /** @param array<mixed> $qualities */
    public static function validate(array $qualities): int
    {
        $amount = 0;
        foreach ($qualities as $steps => $count) {
            if (!is_int($steps) || !is_int($count) || $steps < 0 || $steps > 70 || $count < 0) {
                throw new InvalidArgumentException('Invalid module quality packet');
            }
            $amount += $count;
        }
        return $amount;
    }
}
