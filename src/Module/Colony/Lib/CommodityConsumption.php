<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\ColonyInterface;

final class CommodityConsumption implements CommodityConsumptionInterface
{
    #[Override]
    public function getConsumption(
        array $production,
        ColonyInterface $colony
    ): array {
        $depositMinings = $colony->getUserDepositMinings();
        $storages = $colony->getStorage();
        $ret = [];
        foreach ($production as $commodityId => $productionItem) {
            $proc = $productionItem->getProduction();
            if ($proc >= 0) {
                continue;
            }

            $commodity = $productionItem->getCommodity();
            $ret[$commodityId]['commodity'] = $commodity;
            $ret[$commodityId]['production'] = $productionItem->getProduction();

            if (array_key_exists($commodityId, $depositMinings)) {
                $deposit = $depositMinings[$commodityId];
                $ret[$commodityId]['turnsleft'] = (int) floor($deposit->getAmountLeft() / abs($proc));
            } else {
                $storage = $storages->get($commodityId);
                $stored = $storage !== null ? $storage->getAmount() : 0;
                $ret[$commodityId]['turnsleft'] = (int) floor($stored / abs($proc));
            }
        }

        return $ret;
    }
}
