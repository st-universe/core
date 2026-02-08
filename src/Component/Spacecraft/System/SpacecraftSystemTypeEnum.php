<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use BadMethodCallException;
use InvalidArgumentException;

enum SpacecraftSystemTypeEnum: int
{
    case HULL = 0;
    case EPS = 1;
    case IMPULSEDRIVE = 2;
    case WARPCORE = 3;
    case COMPUTER = 4;
    case PHASER = 5;
    case TORPEDO = 6;
    case CLOAK = 7;
    case LSS = 8;
    case NBS = 9;
    case WARPDRIVE = 10;
    case SHIELDS = 11;
    case TACHYON_SCANNER = 12;
    case LIFE_SUPPORT = 13;
    case TRACTOR_BEAM = 14;
    case TROOP_QUARTERS = 15;
    case DEFLECTOR = 16;
    case ASTRO_LABORATORY = 17;
    case SUBSPACE_SCANNER = 18;
    case MATRIX_SCANNER = 19;
    case TORPEDO_STORAGE = 20;
    case SHUTTLE_RAMP = 21;
    case BEAM_BLOCKER = 22;
    case CONSTRUCTION_HUB = 23;
    case UPLINK = 24;
    case FUSION_REACTOR = 25;
    case TRANSWARP_COIL = 26;
    case TRACKER = 27;
    case THOLIAN_WEB = 28;
    case RPG_MODULE = 29;
    case SINGULARITY_REACTOR = 30;
    case BUSSARD_COLLECTOR = 31;
    case AGGREGATION_SYSTEM = 32;
    case WARPDRIVE_BOOSTER = 33;
    case WARPCORE_CHARGE_TRANSFER = 34;

    public function getDescription(): string
    {
        return match ($this) {
            self::HULL => "Hülle",
            self::CLOAK => "Tarnung",
            self::NBS => "Nahbereichssensoren",
            self::LSS => "Langstreckensensoren",
            self::PHASER => "Strahlenwaffe",
            self::TORPEDO => "Projektilwaffe",
            self::WARPDRIVE => "Warpantrieb",
            self::EPS => "Energiesystem",
            self::IMPULSEDRIVE => "Impulsantrieb",
            self::COMPUTER => "Computer",
            self::SHIELDS => "Schilde",
            self::WARPCORE => "Warpkern",
            self::TACHYON_SCANNER => "Tachyonscanner",
            self::LIFE_SUPPORT => "Lebenserhaltungssystem",
            self::TRACTOR_BEAM => "Traktorstrahl",
            self::TROOP_QUARTERS => "Truppenquartiere",
            self::DEFLECTOR => "Deflektor",
            self::ASTRO_LABORATORY => "Astrometrisches Labor",
            self::SUBSPACE_SCANNER => "Subraumfeldsensoren",
            self::MATRIX_SCANNER => "Matrixsensoren",
            self::TORPEDO_STORAGE => "Torpedolager",
            self::SHUTTLE_RAMP => "Shuttlerampe",
            self::BEAM_BLOCKER => "Beamblocker",
            self::CONSTRUCTION_HUB => "Werfthub",
            self::UPLINK => "Uplink",
            self::FUSION_REACTOR => "Fusionsreaktor",
            self::TRANSWARP_COIL => "Transwarpspule",
            self::TRACKER => "Tracker",
            self::THOLIAN_WEB => "Netzemitter",
            self::RPG_MODULE => "RPG-Modul",
            self::SINGULARITY_REACTOR => "Singularitätsreaktor",
            self::BUSSARD_COLLECTOR => "Bussardkollektor",
            self::AGGREGATION_SYSTEM => "Aggregationssystem",
            self::WARPDRIVE_BOOSTER => "Warpantrieb Booster",
            self::WARPCORE_CHARGE_TRANSFER => "Warpkern Ladungstransfer",
        };
    }

    public function getGenericTemplate(): ?string
    {
        return match ($this) {
            self::ASTRO_LABORATORY,
            self::SHIELDS,
            self::RPG_MODULE,
            self::TACHYON_SCANNER,
            self::CONSTRUCTION_HUB,
            self::UPLINK,
            self::WARPDRIVE,
            self::CLOAK => 'html/spacecraft/system/systemWithOnOff.twig',
            self::THOLIAN_WEB,
            self::SUBSPACE_SCANNER,
            self::AGGREGATION_SYSTEM,
            self::BUSSARD_COLLECTOR,
            self::WARPCORE_CHARGE_TRANSFER => 'html/spacecraft/system/openSettingsWhenHealthy.twig',
            default => null
        };
    }

    /**
     * the higher the number, the more important the system is
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::LIFE_SUPPORT => 10,
            self::EPS => 6,
            self::WARPCORE,
            self::FUSION_REACTOR => 5,
            self::DEFLECTOR => 4,
            self::TROOP_QUARTERS,
            self::WARPDRIVE => 3,
            self::LSS,
            self::NBS => 2,
            self::SUBSPACE_SCANNER,
            self::CLOAK => 0,
            default => 1
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SHIELDS => 'shldac',
            self::PHASER => 'act_phaser',
            self::TORPEDO => 'act_torp',
            self::SUBSPACE_SCANNER => 'subspace',
            self::CLOAK => 'tarn',
            self::TACHYON_SCANNER => 'decloak',
            self::RPG_MODULE => 'rpg',
            self::ASTRO_LABORATORY => 'map',
            self::CONSTRUCTION_HUB => 'rep',
            self::THOLIAN_WEB => 'net',
            self::AGGREGATION_SYSTEM => 'aggsys',
            self::BUSSARD_COLLECTOR => 'bussard',
            self::UPLINK => 'uplink',
            self::WARPDRIVE => 'warp',
            self::WARPCORE_CHARGE_TRANSFER => 'wct',
            default => throw new BadMethodCallException(sprintf('no icon defined for system type: %s', $this->name))
        };
    }

    public function isReloadOnActivation(): bool
    {
        return match ($this) {
            self::SUBSPACE_SCANNER => true,
            default => false
        };
    }

    public function canBeDamaged(): bool
    {
        return match ($this) {
            self::RPG_MODULE => false,
            default => true
        };
    }

    public static function getByName(string $name): SpacecraftSystemTypeEnum
    {
        foreach (self::cases() as $type) {
            if ($type->name === $name) {
                return $type;
            }
        }

        throw new InvalidArgumentException(sprintf('unknown spacecraft system type name: %s', $name));
    }
}
