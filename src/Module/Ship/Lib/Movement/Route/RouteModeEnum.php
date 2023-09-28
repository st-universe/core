<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

final class RouteModeEnum
{
    public const ROUTE_MODE_FLIGHT = 1;
    public const ROUTE_MODE_SYSTEM_ENTRY = 2;
    public const ROUTE_MODE_SYSTEM_EXIT = 3;
    public const ROUTE_MODE_WORMHOLE_ENTRY = 4;
    public const ROUTE_MODE_WORMHOLE_EXIT = 5;
    public const ROUTE_MODE_TRANSWARP = 6;
}
