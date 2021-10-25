<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

final class CommodityTypeEnum
{
    public const GOOD_TYPE_STANDARD = 1;
    public const GOOD_TYPE_EFFECT = 2;

    // commodity IDs
    public const GOOD_FOOD = 1;
    public const GOOD_BUILDING_MATERIALS = 2;
    public const GOOD_DEUTERIUM = 5;
    public const GOOD_ANTIMATTER = 6;
    public const GOOD_DILITHIUM = 8;
    public const GOOD_DURANIUM = 21;
    public const GOOD_LATINUM = 50;
    public const GOOD_SPARE_PART = 10001;
    public const GOOD_SYSTEM_COMPONENT = 10002;

    //base value for e.g. shuttles
    public const BASE_ID_WORKBEE = 20060;
    public const BASE_IDS_SHUTTLE =  [self::BASE_ID_WORKBEE];

    public static function getDescription(int $commodityId): string
    {
        switch ($commodityId) {
            case CommodityTypeEnum::GOOD_DEUTERIUM:
                return _("Deuterium");
            case CommodityTypeEnum::GOOD_ANTIMATTER:
                return _("Antimaterie");
            case CommodityTypeEnum::GOOD_DILITHIUM:
                return _("Dilithium");
        }
        return '';
    }
}
