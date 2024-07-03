<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipLSSModeEnum
{
    // LSS modes
    public const int LSS_NORMAL = 1;
    public const int LSS_BORDER = 2;



    public static function getDescription(int $lssMode): string
    {
        return match ($lssMode) {
            ShipLSSModeEnum::LSS_NORMAL => _("Territorialansicht deaktivieren"),
            ShipLSSModeEnum::LSS_BORDER => _("Territorialansicht aktivieren"),
            default => '',
        };
    }
}
