<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;

final class CommodityConsumption implements CommodityConsumptionInterface
{
    public function __construct(
        private readonly ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository
    ) {}

    #[\Override]
    public function getConsumption(
        array $production,
        Colony $colony
    ): array {
        $depositMinings = $this->colonyDepositMiningRepository->getCurrentUserDepositMinings($colony);
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
