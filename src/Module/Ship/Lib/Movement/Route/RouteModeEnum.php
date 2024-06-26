<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

enum RouteModeEnum: int
{
    case ROUTE_MODE_FLIGHT = 1;
    case ROUTE_MODE_SYSTEM_ENTRY = 2;
    case ROUTE_MODE_SYSTEM_EXIT = 3;
    case ROUTE_MODE_WORMHOLE_ENTRY = 4;
    case ROUTE_MODE_WORMHOLE_EXIT = 5;
    case ROUTE_MODE_TRANSWARP = 6;
}
