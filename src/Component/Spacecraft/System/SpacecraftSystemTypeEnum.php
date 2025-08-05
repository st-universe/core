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

    public function getDescription(): string
    {
        return match ($this) {
            Self::HULL => "Hülle",
            Self::CLOAK => "Tarnung",
            Self::NBS => "Nahbereichssensoren",
            Self::LSS => "Langstreckensensoren",
            Self::PHASER => "Strahlenwaffe",
            Self::TORPEDO => "Projektilwaffe",
            Self::WARPDRIVE => "Warpantrieb",
            Self::EPS => "Energiesystem",
            Self::IMPULSEDRIVE => "Impulsantrieb",
            Self::COMPUTER => "Computer",
            Self::SHIELDS => "Schilde",
            Self::WARPCORE => "Warpkern",
            Self::TACHYON_SCANNER => "Tachyonscanner",
            Self::LIFE_SUPPORT => "Lebenserhaltungssystem",
            Self::TRACTOR_BEAM => "Traktorstrahl",
            Self::TROOP_QUARTERS => "Truppenquartiere",
            Self::DEFLECTOR => "Deflektor",
            Self::ASTRO_LABORATORY => "Astrometrisches Labor",
            Self::SUBSPACE_SCANNER => "Subraumfeldsensoren",
            Self::MATRIX_SCANNER => "Matrixsensoren",
            Self::TORPEDO_STORAGE => "Torpedolager",
            Self::SHUTTLE_RAMP => "Shuttlerampe",
            Self::BEAM_BLOCKER => "Beamblocker",
            Self::CONSTRUCTION_HUB => "Werfthub",
            Self::UPLINK => "Uplink",
            Self::FUSION_REACTOR => "Fusionsreaktor",
            Self::TRANSWARP_COIL => "Transwarpspule",
            Self::TRACKER => "Tracker",
            Self::THOLIAN_WEB => "Netzemitter",
            Self::RPG_MODULE => "RPG-Modul",
            Self::SINGULARITY_REACTOR => "Singularitätsreaktor",
            Self::BUSSARD_COLLECTOR => "Bussardkollektor",
            Self::AGGREGATION_SYSTEM => "Aggregationssystem",
            Self::WARPDRIVE_BOOSTER => "Warpantrieb Booster"
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
            self::BUSSARD_COLLECTOR => 'html/spacecraft/system/openSettingsWhenHealthy.twig',
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
