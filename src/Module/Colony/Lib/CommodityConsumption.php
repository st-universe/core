<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class CommodityConsumption implements CommodityConsumptionInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->commodityRepository = $commodityRepository;
    }

    public function getConsumption(ColonyInterface $colony): array
    {
        $depositMinings = $colony->getUserDepositMinings();
        $stor = $colony->getStorage();
        $prod = $colony->getProduction();
        $ret = [];
        foreach ($prod as $commodityId => $productionItem) {
            $proc = $productionItem->getProduction();
            if ($proc >= 0) {
                continue;
            }
            $ret[$commodityId]['commodity'] = $this->commodityRepository->find((int)$productionItem->getCommodityId());
            $ret[$commodityId]['production'] = $productionItem->getProduction();

            if (array_key_exists($commodityId, $depositMinings)) {
                $deposit = $depositMinings[$commodityId];
                $ret[$commodityId]['turnsleft'] = floor($deposit->getAmountLeft() / abs($proc));
            } else {
                $stored = $stor->containsKey($commodityId) ? $stor[$commodityId]->getAmount() : 0;
                $ret[$commodityId]['turnsleft'] = floor($stored / abs($proc));
            }
        }
        return $ret;
    }
}
