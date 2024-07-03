<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

final class CommodityTypeEnum
{
    // commodity types
    public const int COMMODITY_TYPE_STANDARD = 1;
    public const int COMMODITY_TYPE_EFFECT = 2;

    // commodity IDs
    public const int COMMODITY_FOOD = 1;
    public const int COMMODITY_BUILDING_MATERIALS = 2;
    public const int COMMODITY_TRANSPARENT_ALUMINIUM = 4;
    public const int COMMODITY_DEUTERIUM = 5;
    public const int COMMODITY_ANTIMATTER = 6;
    public const int COMMODITY_PLASMA = 7;
    public const int COMMODITY_DILITHIUM = 8;
    public const int COMMODITY_DURANIUM = 21;
    public const int COMMODITY_LATINUM = 50;
    public const int COMMODITY_SPARE_PART = 10001;
    public const int COMMODITY_SYSTEM_COMPONENT = 10002;

    // research commodities
    public const int COMMODITY_RESEARCH_LVL1 = 1701;
    public const int COMMODITY_RESEARCH_LVL2 = 1702;
    public const int COMMODITY_RESEARCH_LVL3 = 1703;
    public const array COMMODITY_RESEARCH_LVL4 = [1711, 1712, 1721, 1722, 1731];


    //effects
    public const int COMMODITY_EFFECT_LIFE_STANDARD = 1300;

    //base value for e.g. shuttles
    public const int BASE_ID_WORKBEE = 20060;
    public const array BASE_IDS_SHUTTLE =  [self::BASE_ID_WORKBEE];
    public const int BASE_ID_BUOY = 88;

    public const int COMMODITY_ADVENT_POINT = 60;

    public static function getDescription(int $commodityId): string
    {
        return match ($commodityId) {
            CommodityTypeEnum::COMMODITY_DEUTERIUM => _("Deuterium"),
            CommodityTypeEnum::COMMODITY_ANTIMATTER => _("Antimaterie"),
            CommodityTypeEnum::COMMODITY_PLASMA => _("Plasma"),
            CommodityTypeEnum::COMMODITY_DILITHIUM => _("Dilithium"),
            CommodityTypeEnum::COMMODITY_SPARE_PART => _("Ersatzteil"),
            CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT => _("Systemkomponente"),
            default => '',
        };
    }
}