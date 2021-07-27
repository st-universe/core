<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Station;

use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\ShipInterface;

final class StationUtility
{
    public static function canShipBuildConstruction(ShipInterface $ship): bool
    {

        // check if ship has at least 5 workbees
        $workbeeCount = 0;
        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isWorkbee()) {
                $workbeeCount += $stor->getAmount();
            }
        }
        if ($workbeeCount < 5) {
            return false;
        }

        return true;

        // check if ship has at least 200 bm and 100 dura
        if (
            !$ship->getStorage()->containsKey(CommodityTypeEnum::GOOD_BUILDING_MATERIALS)
            || $ship->getStorage()->get(CommodityTypeEnum::GOOD_BUILDING_MATERIALS)->getAmount() < 100
        ) {
            return false;
        }
        if (
            !$ship->getStorage()->containsKey(CommodityTypeEnum::GOOD_DURANIUM)
            || $ship->getStorage()->get(CommodityTypeEnum::GOOD_DURANIUM)->getAmount() < 50
        ) {
            return false;
        }

        return true;
    }
}
