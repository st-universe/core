<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

enum SpacecraftSystemModeEnum: int
{
    case MODE_ALWAYS_OFF = 0;
    case MODE_OFF = 1;
    case MODE_ON = 2;
    case MODE_ALWAYS_ON = 3;

    public function isActivated(): bool
    {
        return match ($this) {
            self::MODE_ALWAYS_OFF,
            self::MODE_OFF => false,
            self::MODE_ON,
            self::MODE_ALWAYS_ON => true
        };
    }
}
