<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;

final class EffectsProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(
        private readonly ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly CommodityCacheInterface $commodityCache
    ) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {
        $commodities = $this->commodityCache->getAll(CommodityTypeConstants::COMMODITY_TYPE_EFFECT);

        $depositMinings = $entity instanceof Colony ? $this->colonyDepositMiningRepository->getCurrentUserDepositMinings($entity) : [];
        $prod = $this->colonyLibFactory->createColonyCommodityProduction($entity)->getProduction();

        $effects = [];
        foreach ($commodities as $value) {
            $commodityId = $value->getId();

            //skip deposit effects on asteroid
            if (array_key_exists($commodityId, $depositMinings)) {
                continue;
            }

            if (!array_key_exists($commodityId, $prod) || $prod[$commodityId]->getProduction() === 0) {
                continue;
            }
            $effects[$commodityId]['commodity'] = $value;
            $effects[$commodityId]['production'] = $prod[$commodityId];
        }

        $template->setTemplateVar('EFFECTS', $effects);
    }
}
