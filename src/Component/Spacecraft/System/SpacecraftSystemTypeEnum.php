<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

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

    public function getDescription(): string
    {
        return match ($this) {
            SpacecraftSystemTypeEnum::SYSTEM_HULL => _("Hülle"),
            SpacecraftSystemTypeEnum::SYSTEM_CLOAK => _("Tarnung"),
            SpacecraftSystemTypeEnum::SYSTEM_NBS => _("Nahbereichssensoren"),
            SpacecraftSystemTypeEnum::SYSTEM_LSS => _("Langstreckensensoren"),
            SpacecraftSystemTypeEnum::SYSTEM_PHASER => _("Energiewaffe"),
            SpacecraftSystemTypeEnum::SYSTEM_TORPEDO => _("Projektilwaffe"),
            SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE => _("Warpantrieb"),
            SpacecraftSystemTypeEnum::SYSTEM_EPS => _("Energiesystem"),
            SpacecraftSystemTypeEnum::SYSTEM_IMPULSEDRIVE => _("Impulsantrieb"),
            SpacecraftSystemTypeEnum::SYSTEM_COMPUTER => _('Computer'),
            SpacecraftSystemTypeEnum::SYSTEM_SHIELDS => _('Schilde'),
            SpacecraftSystemTypeEnum::SYSTEM_WARPCORE => _('Warpkern'),
            SpacecraftSystemTypeEnum::SYSTEM_TACHYON_SCANNER => _('Tachyonscanner'),
            SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT => _('Lebenserhaltungssystem'),
            SpacecraftSystemTypeEnum::SYSTEM_TRACTOR_BEAM => _('Traktorstrahl'),
            SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS => _('Truppenquartiere'),
            SpacecraftSystemTypeEnum::SYSTEM_DEFLECTOR => _('Deflektor'),
            SpacecraftSystemTypeEnum::SYSTEM_ASTRO_LABORATORY => _('Astrometrisches Labor'),
            SpacecraftSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER => _('Subraumfeldsensoren'),
            SpacecraftSystemTypeEnum::SYSTEM_MATRIX_SCANNER => _('Matrixsensoren'),
            SpacecraftSystemTypeEnum::SYSTEM_TORPEDO_STORAGE => _('Torpedolager'),
            SpacecraftSystemTypeEnum::SYSTEM_SHUTTLE_RAMP => _('Shuttlerampe'),
            SpacecraftSystemTypeEnum::SYSTEM_BEAM_BLOCKER => _('Beamblocker'),
            SpacecraftSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB => _('Werfthub'),
            SpacecraftSystemTypeEnum::SYSTEM_UPLINK => _('Uplink'),
            SpacecraftSystemTypeEnum::SYSTEM_FUSION_REACTOR => _('Fusionsreaktor'),
            SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL => _('Transwarpspule'),
            SpacecraftSystemTypeEnum::SYSTEM_TRACKER => _('Tracker'),
            SpacecraftSystemTypeEnum::SYSTEM_THOLIAN_WEB => _('Netzemitter'),
            SpacecraftSystemTypeEnum::SYSTEM_RPG_MODULE => _('RPG-Modul'),
            SpacecraftSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR => _('Singularitätsreaktor'),
            SpacecraftSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR => _('Bussard-Kollector'),
            SpacecraftSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM => _('Aggregationssystem'),
        };
    }
}
