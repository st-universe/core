<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

final class ShipSystemTypeEnum
{
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

    public const SYSTEM_ECOST_DOCK = 1;

    //TODO use this method in ActivatorDeactivatorHelper
    public static function getDescription(int $systemType): string {
        switch ($systemType) {
            case ShipSystemTypeEnum::SYSTEM_CLOAK:
                return "Tarnung";
            case ShipSystemTypeEnum::SYSTEM_NBS:
                return "Nahbereichssensoren";
            case ShipSystemTypeEnum::SYSTEM_LSS:
                return "Langstreckensensoren";
            case ShipSystemTypeEnum::SYSTEM_PHASER:
                return "Strahlenwaffe";
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return "Torpedobänke";
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return "Warpantrieb";
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return _("Energiesystem");
            case ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE:
                return _("Impulsantrieb");
            case ShipSystemTypeEnum::SYSTEM_COMPUTER:
                return _('Computer');
            case ShipSystemTypeEnum::SYSTEM_WARPCORE:
                return _('Warpkern');
            case ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER:
                return _('Tachyon-Scanner');
            case ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT:
                return _('Lebenserhaltungssysteme');
            case ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM:
                return _('Traktorstrahl');
        }
        return '';
    }
}
