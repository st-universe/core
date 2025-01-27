<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

enum RouteModeEnum: int
{
    case FLIGHT = 1;
    case SYSTEM_ENTRY = 2;
    case SYSTEM_EXIT = 3;
    case WORMHOLE_ENTRY = 4;
    case WORMHOLE_EXIT = 5;
    case TRANSWARP = 6;
}
