<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

enum ShipSystemTypeEnum: int
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

    public function getDescription(): string
    {
        return match ($this) {
            ShipSystemTypeEnum::SYSTEM_HULL => _("Hülle"),
            ShipSystemTypeEnum::SYSTEM_CLOAK => _("Tarnung"),
            ShipSystemTypeEnum::SYSTEM_NBS => _("Nahbereichssensoren"),
            ShipSystemTypeEnum::SYSTEM_LSS => _("Langstreckensensoren"),
            ShipSystemTypeEnum::SYSTEM_PHASER => _("Energiewaffe"),
            ShipSystemTypeEnum::SYSTEM_TORPEDO => _("Projektilwaffe"),
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE => _("Warpantrieb"),
            ShipSystemTypeEnum::SYSTEM_EPS => _("Energiesystem"),
            ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE => _("Impulsantrieb"),
            ShipSystemTypeEnum::SYSTEM_COMPUTER => _('Computer'),
            ShipSystemTypeEnum::SYSTEM_SHIELDS => _('Schilde'),
            ShipSystemTypeEnum::SYSTEM_WARPCORE => _('Warpkern'),
            ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER => _('Tachyonscanner'),
            ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT => _('Lebenserhaltungssystem'),
            ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM => _('Traktorstrahl'),
            ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS => _('Truppenquartiere'),
            ShipSystemTypeEnum::SYSTEM_DEFLECTOR => _('Deflektor'),
            ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY => _('Astrometrisches Labor'),
            ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER => _('Subraumfeldsensoren'),
            ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER => _('Matrixsensoren'),
            ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE => _('Torpedolager'),
            ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP => _('Shuttlerampe'),
            ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER => _('Beamblocker'),
            ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB => _('Werfthub'),
            ShipSystemTypeEnum::SYSTEM_UPLINK => _('Uplink'),
            ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR => _('Fusionsreaktor'),
            ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL => _('Transwarpspule'),
            ShipSystemTypeEnum::SYSTEM_TRACKER => _('Tracker'),
            ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB => _('Netzemitter'),
            ShipSystemTypeEnum::SYSTEM_RPG_MODULE => _('RPG-Modul'),
            ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR => _('Singularitätsreaktor'),
            ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR => _('Bussard-Kollector'),
        };
    }
}
