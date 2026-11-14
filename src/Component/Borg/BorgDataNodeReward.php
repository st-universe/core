<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class BorgDataNodeReward
{
    public function __construct(private CommodityRepositoryInterface $commodities, private StorageManagerInterface $storage, private StuRandom $random) {}

    public function findCommodity(?BorgSphereConfiguration $configuration): ?Commodity
    {
        if ($configuration === null || !$configuration->isAssimilationEnabled() || $this->chance($configuration) === 0) {
            return null;
        }
        $commodityId = $configuration->getSettings()['data_node_commodity_id'] ?? 0;
        return $commodityId > 0 ? $this->commodities->find($commodityId) : null;
    }

    public function grant(Spacecraft $ship, ?BorgSphereConfiguration $configuration, ?Commodity $commodity = null): string
    {
        if ($configuration === null || !$configuration->isAssimilationEnabled()) {
            return '';
        }
        $commodity ??= $this->findCommodity($configuration);
        if ($commodity === null || $ship->getStorageSum() >= $ship->getMaxStorage()) {
            return '';
        }
        if ($this->random->rand(1, 100) > $this->chance($configuration)) {
            return '';
        }
        $this->storage->upperStorage($ship, $commodity, 1);
        return sprintf('. Zusätzlich wurde 1 %s dem Lagerraum hinzugefügt', $commodity->getName());
    }

    private function chance(BorgSphereConfiguration $configuration): int
    {
        return max(0, min(100, $configuration->getSettings()['data_node_chance_percent'] ?? 5));
    }
}
