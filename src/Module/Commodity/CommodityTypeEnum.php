<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

final class CommodityTypeEnum
{
    // commodity types
    public const COMMODITY_TYPE_STANDARD = 1;
    public const COMMODITY_TYPE_EFFECT = 2;

    // commodity IDs
    public const COMMODITY_FOOD = 1;
    public const COMMODITY_BUILDING_MATERIALS = 2;
    public const COMMODITY_TRANSPARENT_ALUMINIUM = 4;
    public const COMMODITY_DEUTERIUM = 5;
    public const COMMODITY_ANTIMATTER = 6;
    public const COMMODITY_PLASMA = 7;
    public const COMMODITY_DILITHIUM = 8;
    public const COMMODITY_DURANIUM = 21;
    public const COMMODITY_LATINUM = 50;
    public const COMMODITY_SPARE_PART = 10001;
    public const COMMODITY_SYSTEM_COMPONENT = 10002;

    // research commodities
    public const COMMODITY_RESEARCH_LVL1 = 1701;
    public const COMMODITY_RESEARCH_LVL2 = 1702;
    public const COMMODITY_RESEARCH_LVL3 = 1703;
    public const COMMODITY_RESEARCH_LVL4 = [1711, 1712, 1721, 1731];


    //effects
    public const COMMODITY_EFFECT_LIFE_STANDARD = 1300;

    //base value for e.g. shuttles
    public const BASE_ID_WORKBEE = 20060;
    public const BASE_IDS_SHUTTLE =  [self::BASE_ID_WORKBEE];

    public const COMMODITY_ADVENT_POINT = 60;

    public static function getDescription(int $commodityId): string
    {
        switch ($commodityId) {
            case CommodityTypeEnum::COMMODITY_DEUTERIUM:
                return _("Deuterium");
            case CommodityTypeEnum::COMMODITY_ANTIMATTER:
                return _("Antimaterie");
            case CommodityTypeEnum::COMMODITY_PLASMA:
                return _("Plasma");
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
