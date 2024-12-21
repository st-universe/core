<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use RuntimeException;

enum SpacecraftSystemTypeEnum: int
{
    case SYSTEM_HULL = 0;
    case SYSTEM_EPS = 1;
    case SYSTEM_IMPULSEDRIVE = 2;
    case SYSTEM_WARPCORE = 3;
    case SYSTEM_COMPUTER = 4;
    case SYSTEM_PHASER = 5;
    case SYSTEM_TORPEDO = 6;
    case SYSTEM_CLOAK = 7;
    case SYSTEM_LSS = 8;
    case SYSTEM_NBS = 9;
    case SYSTEM_WARPDRIVE = 10;
    case SYSTEM_SHIELDS = 11;
    case SYSTEM_TACHYON_SCANNER = 12;
    case SYSTEM_LIFE_SUPPORT = 13;
    case SYSTEM_TRACTOR_BEAM = 14;
    case SYSTEM_TROOP_QUARTERS = 15;
    case SYSTEM_DEFLECTOR = 16;
    case SYSTEM_ASTRO_LABORATORY = 17;
    case SYSTEM_SUBSPACE_SCANNER = 18;
    case SYSTEM_MATRIX_SCANNER = 19;
    case SYSTEM_TORPEDO_STORAGE = 20;
    case SYSTEM_SHUTTLE_RAMP = 21;
    case SYSTEM_BEAM_BLOCKER = 22;
    case SYSTEM_CONSTRUCTION_HUB = 23;
    case SYSTEM_UPLINK = 24;
    case SYSTEM_FUSION_REACTOR = 25;
    case SYSTEM_TRANSWARP_COIL = 26;
    case SYSTEM_TRACKER = 27;
    case SYSTEM_THOLIAN_WEB = 28;
    case SYSTEM_RPG_MODULE = 29;
    case SYSTEM_SINGULARITY_REACTOR = 30;
    case SYSTEM_BUSSARD_COLLECTOR = 31;
    case SYSTEM_AGGREGATION_SYSTEM = 32;
    case SYSTEM_WARPDRIVE_BOOSTER = 33;

    public function getDescription(): string
    {
        return match ($this) {
            SpacecraftSystemTypeEnum::SYSTEM_HULL => "Hülle",
            SpacecraftSystemTypeEnum::SYSTEM_CLOAK => "Tarnung",
            SpacecraftSystemTypeEnum::SYSTEM_NBS => "Nahbereichssensoren",
            SpacecraftSystemTypeEnum::SYSTEM_LSS => "Langstreckensensoren",
            SpacecraftSystemTypeEnum::SYSTEM_PHASER => "Energiewaffe",
            SpacecraftSystemTypeEnum::SYSTEM_TORPEDO => "Projektilwaffe",
            SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE => "Warpantrieb",
            SpacecraftSystemTypeEnum::SYSTEM_EPS => "Energiesystem",
            SpacecraftSystemTypeEnum::SYSTEM_IMPULSEDRIVE => "Impulsantrieb",
            SpacecraftSystemTypeEnum::SYSTEM_COMPUTER => "Computer",
            SpacecraftSystemTypeEnum::SYSTEM_SHIELDS => "Schilde",
            SpacecraftSystemTypeEnum::SYSTEM_WARPCORE => "Warpkern",
            SpacecraftSystemTypeEnum::SYSTEM_TACHYON_SCANNER => "Tachyonscanner",
            SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT => "Lebenserhaltungssystem",
            SpacecraftSystemTypeEnum::SYSTEM_TRACTOR_BEAM => "Traktorstrahl",
            SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS => "Truppenquartiere",
            SpacecraftSystemTypeEnum::SYSTEM_DEFLECTOR => "Deflektor",
            SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY => "Astrometrisches Labor",
            SpacecraftSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER => "Subraumfeldsensoren",
            SpacecraftSystemTypeEnum::SYSTEM_MATRIX_SCANNER => "Matrixsensoren",
            SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE => "Torpedolager",
            SpacecraftSystemTypeEnum::SYSTEM_SHUTTLE_RAMP => "Shuttlerampe",
            SpacecraftSystemTypeEnum::SYSTEM_BEAM_BLOCKER => "Beamblocker",
            SpacecraftSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB => "Werfthub",
            SpacecraftSystemTypeEnum::SYSTEM_UPLINK => "Uplink",
            SpacecraftSystemTypeEnum::SYSTEM_FUSION_REACTOR => "Fusionsreaktor",
            SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL => "Transwarpspule",
            SpacecraftSystemTypeEnum::SYSTEM_TRACKER => "Tracker",
            SpacecraftSystemTypeEnum::SYSTEM_THOLIAN_WEB => "Netzemitter",
            SpacecraftSystemTypeEnum::SYSTEM_RPG_MODULE => "RPG-Modul",
            SpacecraftSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR => "Singularitätsreaktor",
            SpacecraftSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR => "Bussardkollektor",
            SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM => "Aggregationssystem",
            SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE_BOOSTER => "Warpantrieb Booster"
        };
    }

    public static function getByName(string $name): SpacecraftSystemTypeEnum
    {
        foreach (self::cases() as $type) {
            if ($type->name === $name) {
                return $type;
            }
        }

        throw new RuntimeException(sprintf('unknown spacecraft system type name: %s', $name));
    }
}
