<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftLssModeEnum: int
{
    case NORMAL = 1;
    case BORDER = 2;

    public function getDescription(): string
    {
        return match ($this) {
            self::NORMAL => "Territorialansicht deaktivieren",
            self::BORDER => "Territorialansicht aktivieren"
        };
    }

    public function isBorderMode(): bool
    {
        return match ($this) {
            self::NORMAL => false,
            self::BORDER => true
        };
    }
}
