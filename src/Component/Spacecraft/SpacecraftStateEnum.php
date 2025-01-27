<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftStateEnum: int
{
    case SHIP_STATE_NONE = 0;
    case SHIP_STATE_REPAIR_PASSIVE = 1;
    case SHIP_STATE_ASTRO_FINALIZING = 2;
    case SHIP_STATE_UNDER_CONSTRUCTION = 3;
    case SHIP_STATE_REPAIR_ACTIVE = 4;
    case SHIP_STATE_UNDER_SCRAPPING = 5;
    case SHIP_STATE_DESTROYED = 6;
    case SHIP_STATE_WEB_SPINNING = 7;
    case SHIP_STATE_ACTIVE_TAKEOVER = 8;
    case SHIP_STATE_GATHER_RESOURCES = 9;
    case SHIP_STATE_RETROFIT = 10;

    public function isActiveState(): bool
    {
        return match ($this) {
            self::SHIP_STATE_NONE,
            self::SHIP_STATE_REPAIR_PASSIVE,
            self::SHIP_STATE_UNDER_CONSTRUCTION,
            self::SHIP_STATE_UNDER_SCRAPPING,
            self::SHIP_STATE_RETROFIT => false,
            default => true
        };
    }
}
