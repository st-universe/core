<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

final class CommodityTypeEnum
{
    public const COMMODITY_TYPE_STANDARD = 1;
    public const COMMODITY_TYPE_EFFECT = 2;

    // commodity IDs
    public const COMMODITY_FOOD = 1;
    public const COMMODITY_BUILDING_MATERIALS = 2;
    public const COMMODITY_TRANSPARENT_ALUMINIUM = 4;
    public const COMMODITY_DEUTERIUM = 5;
    public const COMMODITY_ANTIMATTER = 6;
    public const COMMODITY_DILITHIUM = 8;
    public const COMMODITY_DURANIUM = 21;
    public const COMMODITY_LATINUM = 50;
    public const COMMODITY_SPARE_PART = 10001;
    public const COMMODITY_SYSTEM_COMPONENT = 10002;

    //effects
    public const COMMODITY_EFFECT_LIFE_STANDARD = 1300;

    //base value for e.g. shuttles
    public const BASE_ID_WORKBEE = 20060;
    public const BASE_IDS_SHUTTLE =  [self::BASE_ID_WORKBEE];

    /**
     * @deprecated not in use, but kept for documentation reasons
     */
    public const COMMODITY_ADVENT_POINT = 60;

    public static function getDescription(int $commodityId): string
    {
        switch ($commodityId) {
            case CommodityTypeEnum::COMMODITY_DEUTERIUM:
                return _("Deuterium");
            case CommodityTypeEnum::COMMODITY_ANTIMATTER:
                return _("Antimaterie");
            case CommodityTypeEnum::COMMODITY_DILITHIUM:
                return _("Dilithium");
            case CommodityTypeEnum::COMMODITY_SPARE_PART:
                return _("Ersatzteil");
            case CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT:
                return _("Systemkomponente");
        }
        return '';
    }
}
