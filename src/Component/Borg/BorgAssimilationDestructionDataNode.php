<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class BorgAssimilationDestructionDataNode
{
    public function __construct(private CommodityRepositoryInterface $commodities, private StorageManagerInterface $storage, private BorgSphereRepositoryInterface $events, private StuRandom $random) {}

    public function grantForDestruction(Spacecraft $ship): ?Commodity
    {
        $entry = $ship->getAssimilation();
        if ($entry === null || (!$entry->isActive() && !$entry->isComplete())) {
            return null;
        }
        $chance = $this->chance($entry);
        if ($chance === 0) {
            return null;
        }
        $commodity = $this->commodities->find(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
        if ($commodity === null || $this->random->rand(1, 100) > $chance) {
            return null;
        }
        $this->storage->upperStorage($ship, $commodity, 1);
        return $commodity;
    }

    private function chance(SpacecraftAssimilated $entry): int
    {
        $source = $entry->getSource();
        $settings = $source === null ? [] : $this->events->getByShip($source)?->getConfiguration()->getSettings() ?? [];
        return max(0, min(100, $settings['data_node_destruction_chance_percent'] ?? 10));
    }
}
