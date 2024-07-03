<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class CommodityConsumption implements CommodityConsumptionInterface
{
    public function __construct(private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function getConsumption(
        array $production,
        ColonyInterface $colony
    ): array {
        $depositMinings = $colony->getUserDepositMinings();
        $stor = $colony->getStorage();
        $ret = [];
        foreach ($production as $commodityId => $productionItem) {
            $proc = $productionItem->getProduction();
            if ($proc >= 0) {
                continue;
            }

            /** @var CommodityInterface $commodity */
            $commodity = $this->commodityRepository->find($productionItem->getCommodityId());
            $ret[$commodityId]['commodity'] = $commodity;
            $ret[$commodityId]['production'] = $productionItem->getProduction();

            if (array_key_exists($commodityId, $depositMinings)) {
                $deposit = $depositMinings[$commodityId];
                $ret[$commodityId]['turnsleft'] = (int) floor($deposit->getAmountLeft() / abs($proc));
            } else {
                $stored = $stor->containsKey($commodityId) ? $stor[$commodityId]->getAmount() : 0;
                $ret[$commodityId]['turnsleft'] = (int) floor($stored / abs($proc));
            }
        }

        return $ret;
    }
}
