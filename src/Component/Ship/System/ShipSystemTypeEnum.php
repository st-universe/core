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

    public static function getDescription(ShipSystemTypeEnum $type): string
    {
        switch ($type) {
            case ShipSystemTypeEnum::SYSTEM_HULL:
                return _("Hülle");
            case ShipSystemTypeEnum::SYSTEM_CLOAK:
                return _("Tarnung");
            case ShipSystemTypeEnum::SYSTEM_NBS:
                return _("Nahbereichssensoren");
            case ShipSystemTypeEnum::SYSTEM_LSS:
                return _("Langstreckensensoren");
            case ShipSystemTypeEnum::SYSTEM_PHASER:
                return _("Energiewaffe");
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return _("Projektilwaffe");
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return _("Warpantrieb");
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return _("Energiesystem");
            case ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE:
                return _("Impulsantrieb");
            case ShipSystemTypeEnum::SYSTEM_COMPUTER:
                return _('Computer');
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
                return _('Schilde');
            case ShipSystemTypeEnum::SYSTEM_WARPCORE:
                return _('Warpkern');
            case ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER:
                return _('Tachyonscanner');
            case ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT:
                return _('Lebenserhaltungssystem');
            case ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM:
                return _('Traktorstrahl');
            case ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS:
                return _('Truppenquartiere');
            case ShipSystemTypeEnum::SYSTEM_DEFLECTOR:
                return _('Deflektor');
            case ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY:
                return _('Astrometrisches Labor');
            case ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER:
                return _('Subraumfeldsensoren');
            case ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER:
                return _('Matrixsensoren');
            case ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE:
                return _('Torpedolager');
            case ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP:
                return _('Shuttlerampe');
            case ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER:
                return _('Beamblocker');
            case ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB:
                return _('Werfthub');
            case ShipSystemTypeEnum::SYSTEM_UPLINK:
                return _('Uplink');
            case ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR:
                return _('Fusionsreaktor');
            case ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL:
                return _('Transwarpspule');
            case ShipSystemTypeEnum::SYSTEM_TRACKER:
                return _('Tracker');
            case ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB:
                return _('Netzemitter');
            case ShipSystemTypeEnum::SYSTEM_RPG_MODULE:
                return _('RPG-Modul');
        }
        return '';
    }
}
