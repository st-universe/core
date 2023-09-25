<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

final class ShipSystemTypeEnum
{
    // system types
    public const SYSTEM_HULL = 0;
    public const SYSTEM_EPS = 1;
    public const SYSTEM_IMPULSEDRIVE = 2;
    public const SYSTEM_WARPCORE = 3;
    public const SYSTEM_COMPUTER = 4;
    public const SYSTEM_PHASER = 5;
    public const SYSTEM_TORPEDO = 6;
    public const SYSTEM_CLOAK = 7;
    public const SYSTEM_LSS = 8;
    public const SYSTEM_NBS = 9;
    public const SYSTEM_WARPDRIVE = 10;
    public const SYSTEM_SHIELDS = 11;
    public const SYSTEM_TACHYON_SCANNER = 12;
    public const SYSTEM_LIFE_SUPPORT = 13;
    public const SYSTEM_TRACTOR_BEAM = 14;
    public const SYSTEM_TROOP_QUARTERS = 15;
    public const SYSTEM_DEFLECTOR = 16;
    public const SYSTEM_ASTRO_LABORATORY = 17;
    public const SYSTEM_SUBSPACE_SCANNER = 18;
    public const SYSTEM_MATRIX_SCANNER = 19;
    public const SYSTEM_TORPEDO_STORAGE = 20;
    public const SYSTEM_SHUTTLE_RAMP = 21;
    public const SYSTEM_BEAM_BLOCKER = 22;
    public const SYSTEM_CONSTRUCTION_HUB = 23;
    public const SYSTEM_UPLINK = 24;
    public const SYSTEM_FUSION_REACTOR = 25;
    public const SYSTEM_TRANSWARP_COIL = 26;
    public const SYSTEM_TRACKER = 27;
    public const SYSTEM_THOLIAN_WEB = 28;
    public const SYSTEM_RPG_MODULE = 29;


    // system priorites
    public const SYSTEM_PRIORITY_STANDARD = 1;
    public const SYSTEM_PRIORITIES = [
        ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT => 10,
        ShipSystemTypeEnum::SYSTEM_EPS => 6,
        ShipSystemTypeEnum::SYSTEM_WARPCORE => 5,
        ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR => 5,
        ShipSystemTypeEnum::SYSTEM_DEFLECTOR => 4,
        ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS => 3,
        ShipSystemTypeEnum::SYSTEM_WARPDRIVE => 3,
        ShipSystemTypeEnum::SYSTEM_LSS => 2,
        ShipSystemTypeEnum::SYSTEM_NBS => 2,
        ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER => 0,
        ShipSystemTypeEnum::SYSTEM_CLOAK => 0
    ];

    // other
    public const SYSTEM_ECOST_DOCK = 1;

    public static function getDescription(int $systemType): string
    {
        switch ($systemType) {
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
