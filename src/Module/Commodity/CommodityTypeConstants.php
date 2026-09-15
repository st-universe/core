<?php

declare(strict_types=1);

namespace Stu\Module\Commodity;

final class CommodityTypeConstants
{
    // commodity types
    public const int COMMODITY_TYPE_STANDARD = 1;
    public const int COMMODITY_TYPE_EFFECT = 2;

    // commodity IDs
    public const int COMMODITY_FOOD = 1;
    public const int COMMODITY_BUILDING_MATERIALS = 2;
    public const int COMMODITY_CHEMICAL_COMPONENTS = 3;
    public const int COMMODITY_TRANSPARENT_ALUMINIUM = 4;
    public const int COMMODITY_DEUTERIUM = 5;
    public const int COMMODITY_ANTIMATTER = 6;
    public const int COMMODITY_PLASMA = 7;
    public const int COMMODITY_DILITHIUM = 8;
    public const int COMMODITY_IRIDIUM_ORE = 11;
    public const int COMMODITY_GALAZIT_ORE = 12;
    public const int COMMODITY_NITRIUM_ORE = 13;
    public const int COMMODITY_MAGNESIT_ORE = 14;
    public const int COMMODITY_KELBONIT_ORE = 15;
    public const int COMMODITY_TALGONIT_ORE = 16;
    public const int COMMODITY_TRITANIUM_ORE = 19;
    public const int COMMODITY_IRIDIUM = 20;
    public const int COMMODITY_HIGH_ENERGY_PLASMA = 32;
    public const int COMMODITY_GALAZIT = 22;
    public const int COMMODITY_NITRIUM = 23;
    public const int COMMODITY_MAGNESIT = 24;
    public const int COMMODITY_KELBONIT = 25;
    public const int COMMODITY_TALGONIT = 26;
    public const int COMMODITY_TRITANIUM = 29;
    public const int COMMODITY_ISOLINEAR_STORAGE_CHIP = 31;
    public const int COMMODITY_NITRIUM_CIRCUITS = 33;
    public const int COMMODITY_SUBSPACE_FIELD_COILS = 34;
    public const int COMMODITY_METAPHASE_CONVERTER = 35;
    public const int COMMODITY_MIKRODYNE_MODULATOR = 36;
    public const int COMMODITY_DEUTERIUM_PARTICLE = 63;
    public const int COMMODITY_ANTIMATTER_PARTICLE = 64;
    public const int COMMODITY_PLASMA_PARTICLE = 65;
    public const int COMMODITY_HIGH_ENERGY_PLASMA_PARTICLE = 66;
    public const int COMMODITY_TRITANIUM_PARTICLE = 67;
    public const int COMMODITY_TALGONIUM_GAS = 68;
    public const int COMMODITY_NITRIUM_PARTICLE = 69;
    public const int COMMODITY_METAPHASIC_GAS = 70;
    public const int COMMODITY_GALONIUM_GAS = 71;
    public const int COMMODITY_KELBONIUM_GAS = 72;
    public const int COMMODITY_DURANIUM = 21;
    public const int COMMODITY_LATINUM = 50;
    public const int COMMODITY_MICRO_PHOTON_TORPEDO = 81;
    public const int COMMODITY_LIGHT_PHOTON_TORPEDO = 82;
    public const int COMMODITY_PHOTON_TORPEDO = 83;
    public const int COMMODITY_QUANTUM_TORPEDO = 84;
    public const int COMMODITY_HEAVY_QUANTUM_TORPEDO = 85;
    public const int COMMODITY_PLASMA_TORPEDO = 86;
    public const int COMMODITY_HEAVY_PLASMA_TORPEDO = 87;
    public const int COMMODITY_EASTER_EGG = 73;
    public const int COMMODITY_SPARE_PART = 10001;
    public const int COMMODITY_SYSTEM_COMPONENT = 10002;

    // research commodities
    public const int COMMODITY_RESEARCH_LVL1 = 1701;
    public const int COMMODITY_RESEARCH_LVL2 = 1702;
    public const int COMMODITY_RESEARCH_LVL3 = 1703;
    public const array COMMODITY_RESEARCH_LVL4 = [1711, 1712, 1721, 1722, 1731];


    //effects
    public const int COMMODITY_EFFECT_LIFE_STANDARD = 1300;
    public const int COMMODITY_EFFECT_UNDERGROUND_LOGISTICS = 1552;
    public const int COMMODITY_EFFECT_ORBITAL_MAINTENANCE = 1801;
    public const int COMMODITY_EFFECT_SHIPYARD_LOGISTICS = 1802;


    //base value for e.g. shuttles
    public const int BASE_ID_WORKBEE = 20060;
    public const array BASE_IDS_SHUTTLE =  [self::BASE_ID_WORKBEE];
    public const int BASE_ID_BUOY = 88;

    public const int COMMODITY_ADVENT_POINT = 60;

    public static function getDescription(int $commodityId): string
    {
        return match ($commodityId) {
            CommodityTypeConstants::COMMODITY_DEUTERIUM => _("Deuterium"),
            CommodityTypeConstants::COMMODITY_ANTIMATTER => _("Antimaterie"),
            CommodityTypeConstants::COMMODITY_PLASMA => _("Plasma"),
            CommodityTypeConstants::COMMODITY_DILITHIUM => _("Dilithium"),
            CommodityTypeConstants::COMMODITY_SPARE_PART => _("Ersatzteil"),
            CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT => _("Systemkomponente"),
            default => '',
        };
    }

    public const array COMMODITY_CONVERSIONS = [
        [self::COMMODITY_DEUTERIUM_PARTICLE, self::COMMODITY_DEUTERIUM, 100, 10, 1],
        [self::COMMODITY_ANTIMATTER_PARTICLE, self::COMMODITY_ANTIMATTER, 150, 3, 1],
        [self::COMMODITY_PLASMA_PARTICLE, self::COMMODITY_PLASMA, 150, 2, 1],
        [self::COMMODITY_HIGH_ENERGY_PLASMA_PARTICLE, self::COMMODITY_HIGH_ENERGY_PLASMA, 1000, 1, 1],
        [self::COMMODITY_TRITANIUM_PARTICLE, self::COMMODITY_TRITANIUM_ORE, 300, 3, 1],
        [self::COMMODITY_TALGONIUM_GAS, self::COMMODITY_TALGONIT_ORE, 100, 8, 2],
        [self::COMMODITY_NITRIUM_PARTICLE, self::COMMODITY_NITRIUM_ORE, 100, 8, 1],
        [self::COMMODITY_METAPHASIC_GAS, self::COMMODITY_MAGNESIT_ORE, 100, 8, 2],
        [self::COMMODITY_GALONIUM_GAS, self::COMMODITY_GALAZIT_ORE, 100, 8, 2],
        [self::COMMODITY_KELBONIUM_GAS, self::COMMODITY_KELBONIT_ORE, 100, 8, 2],
    ];
}
