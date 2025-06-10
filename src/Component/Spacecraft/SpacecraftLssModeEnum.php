<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftLssModeEnum: int
{
    case NORMAL = 1;
    case BORDER = 2;
    case IMPASSABLE = 3;
    case CARTOGRAPHING = 4;

    public function getDescription(): string
    {
        return match ($this) {
            self::NORMAL => "LSS Filter deaktivieren",
            self::BORDER => "Territorialansicht",
            self::IMPASSABLE => "Unpassierbarkeitsansicht",
            self::CARTOGRAPHING => "Kartographieansicht"
        };
    }

    public function isBorderMode(): bool
    {
        return match ($this) {
            self::NORMAL => false,
            default => true
        };
    }
}
