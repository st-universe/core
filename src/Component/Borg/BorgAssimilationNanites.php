<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class BorgAssimilationNanites
{
    public function __construct(private CommodityRepositoryInterface $commodities, private StorageManagerInterface $storage, private StuRandom $random) {}

    public function grant(SpacecraftAssimilated $entry, int $progress): int
    {
        if ($progress <= 0) {
            return 0;
        }
        $commodity = $this->commodities->find($entry->getNaniteCommodityId());
        if ($commodity === null) {
            return 0;
        }
        $amount = (int) ceil($this->random->rand($entry->getNaniteRewardMin(), $entry->getNaniteRewardMax()) * min(10000, $progress) / 10000);
        $this->storage->upperStorage($entry->getSpacecraft(), $commodity, $amount);
        return $amount;
    }
}
