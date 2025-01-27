<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftStateEnum: int
{
    case NONE = 0;
    case REPAIR_PASSIVE = 1;
    case ASTRO_FINALIZING = 2;
    case UNDER_CONSTRUCTION = 3;
    case REPAIR_ACTIVE = 4;
    case UNDER_SCRAPPING = 5;
    case DESTROYED = 6;
    case WEB_SPINNING = 7;
    case ACTIVE_TAKEOVER = 8;
    case GATHER_RESOURCES = 9;
    case RETROFIT = 10;

    public function isActiveState(): bool
    {
        return match ($this) {
            self::NONE,
            self::REPAIR_PASSIVE,
            self::UNDER_CONSTRUCTION,
            self::UNDER_SCRAPPING,
            self::RETROFIT => false,
            default => true
        };
    }
}
