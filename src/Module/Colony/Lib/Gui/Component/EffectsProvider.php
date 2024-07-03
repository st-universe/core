<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;

final class EffectsProvider implements GuiComponentProviderInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private CommodityCacheInterface $commodityCache)
    {
    }

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $commodities = $this->commodityCache->getAll(CommodityTypeEnum::COMMODITY_TYPE_EFFECT);
        $depositMinings = $host instanceof ColonyInterface ? $host->getUserDepositMinings() : [];
        $prod = $this->colonyLibFactory->createColonyCommodityProduction($host)->getProduction();

        $effects = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();

            //skip deposit effects on asteroid
            if (array_key_exists($commodityId, $depositMinings)) {
                continue;
            }

            if (!array_key_exists($commodityId, $prod) || $prod[$commodityId]->getProduction() == 0) {
                continue;
            }
            $effects[$commodityId]['commodity'] = $value;
            $effects[$commodityId]['production'] = $prod[$commodityId];
        }

        $game->setTemplateVar('EFFECTS', $effects);
    }
}
