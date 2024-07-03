<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

enum GuiComponentEnum: int
{
    // mainscreen
    case SHIELDING = 1;
    case EPS_BAR = 2;
    case SURFACE = 3;
    case STORAGE = 4;

    // submenues
    case MANAGEMENT = 5;
    case EFFECTS = 6;
    case BUILD_MENUES = 7;
    case SOCIAL = 8;
    case BUILDING_MANAGEMENT = 9;

    // menues
    case ACADEMY = 10;
    case AIRFIELD = 11;
    case MODULE_FAB = 12;
    case TORPEDO_FAB = 13;
    case SHIPYARD = 14;
    case FIGHTER_SHIPYARD = 15;
    case SHIP_BUILDPLANS = 16;
    case SHIP_REPAIR = 17;
    case SHIP_DISASSEMBLY = 18;
}
