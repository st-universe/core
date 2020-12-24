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

    //TODO Traktorstrahl und andere Systeme aufnehmen?

    public const SYSTEM_ECOST_DOCK = 1;
}
