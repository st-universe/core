<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;

final class StorageProvider implements GuiComponentProviderInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private CommodityCacheInterface $commodityCache)
    {
    }

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $commodities = $this->commodityCache->getAll(CommodityTypeEnum::COMMODITY_TYPE_STANDARD);

        $prod = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();
        $game->setTemplateVar(
            'PRODUCTION_SUM',
            $this->colonyLibFactory->createColonyProductionSumReducer()->reduce($prod)
        );

        $stor = $host instanceof ColonyInterface ? $host->getStorage() : new ArrayCollection();
        $storage = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();
            if (array_key_exists($commodityId, $prod)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['production'] = $prod[$commodityId];
                $storage[$commodityId]['storage'] = $stor->containsKey($commodityId) ? $stor[$commodityId] : false;
            } elseif ($stor->containsKey($commodityId)) {
                $storage[$commodityId]['commodity'] = $value;
                $storage[$commodityId]['storage'] = $stor[$commodityId];
                $storage[$commodityId]['production'] = false;
            }
        }

        $game->setTemplateVar('STORAGE', $storage);
        $game->setTemplateVar('STORAGE_SUM', $this->getStorageSum($host));
        $game->setTemplateVar('STORAGE_SUM_PERCENT', $this->getStorageSumPercent($host));
        $game->setTemplateVar('MAX_STORAGE', $host->getMaxStorage());
    }

    private function getStorageSum(PlanetFieldHostInterface $host): int
    {
        if (!$host instanceof ColonyInterface) {
            return 0;
        }

        return $host->getStorageSum();
    }

    private function getStorageSumPercent(PlanetFieldHostInterface $host): float
    {
        if (!$host instanceof ColonyInterface) {
            return 0;
        }

        $maxStorage = $host->getMaxStorage();

        if ($maxStorage === 0) {
            return 0;
        }

        return round(100 / $maxStorage * $host->getStorageSum(), 2);
    }
}
