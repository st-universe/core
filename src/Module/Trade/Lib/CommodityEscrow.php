<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Lib\Transfer\Storage\ModuleQualityPacket;
use Stu\Orm\Entity\Storage;

final class CommodityEscrow
{
    /**
     * @param array<int, int> $first
     * @param array<int, int> $second
     * @return array<int, int>
     */
    public static function merge(array $first, array $second): array
    {
        ModuleQualityPacket::validate($first);
        ModuleQualityPacket::validate($second);
        foreach ($second as $steps => $amount) {
            $first[$steps] = ($first[$steps] ?? 0) + $amount;
        }
        ksort($first);
        return $first;
    }

    /**
     * @param array<int, int> $qualities
     * @return array{array<int, int>, array<int, int>}
     */
    public static function split(array $qualities, int $amount): array
    {
        $storage = new Storage()->setAmount(ModuleQualityPacket::validate($qualities));
        $storage->addModuleQualityAmounts($qualities);
        $taken = $storage->removeAmount($amount);
        return [$taken, $storage->getModuleQualityAmounts()];
    }
}
