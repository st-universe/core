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
            if (!$stor->containsKey($commodityId)) {
                $ret[$commodityId]['storage'] = 0;
            } else {
                $ret[$commodityId]['storage'] = $stor[$commodityId]->getAmount();
            }
            $ret[$commodityId]['turnsleft'] = floor($ret[$commodityId]['storage'] / abs($proc));
        }
        return $ret;
    }
}
