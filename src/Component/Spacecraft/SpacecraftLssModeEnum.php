<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftLssModeEnum: int
{
    case LSS_NORMAL = 1;
    case LSS_BORDER = 2;

    public function getDescription(): string
    {
        return match ($this) {
            self::LSS_NORMAL => "Territorialansicht deaktivieren",
            self::LSS_BORDER => "Territorialansicht aktivieren"
        };
    }

    public function isBorderMode(): bool
    {
        return match ($this) {
            self::LSS_NORMAL => false,
            self::LSS_BORDER => true
        };
    }
}
