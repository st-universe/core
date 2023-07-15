<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

final class RouteModeEnum
{
    public const ROUTE_MODE_FLIGHT = 0;
    public const ROUTE_MODE_SYSTEM_ENTRY = 1;
    public const ROUTE_MODE_SYSTEM_EXIT = 2;
    public const ROUTE_MODE_WORMHOLE_ENTRY = 3;
    public const ROUTE_MODE_WORMHOLE_EXIT = 4;
}
