<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

enum GuiComponentEnum: string
{
    case SURFACE = "registerSurface";
    case EFFECTS = "registerEffects";
    case STORAGE = "registerStorage";
    case SHIELD_BAR = "registerShieldBar";
    case SHIELDING_MANAGER = "registerShieldingManager";
    case EPS_BAR = "registerEpsBar";
    case BUILDING_MANAGEMENT = "registerBuildingManagement";
}
