<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;

final class EffectsProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private CommodityCacheInterface $commodityCache) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $commodities = $this->commodityCache->getAll(CommodityTypeEnum::COMMODITY_TYPE_EFFECT);
        $depositMinings = $entity instanceof ColonyInterface ? $entity->getUserDepositMinings() : [];
        $prod = $this->colonyLibFactory->createColonyCommodityProduction($entity)->getProduction();

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
