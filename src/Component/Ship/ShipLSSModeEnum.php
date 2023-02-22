<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipLSSModeEnum
{
    // LSS modes
    public const LSS_NORMAL = 1;
    public const LSS_BORDER = 2;



    public static function getDescription(int $lssMode): string
    {
        switch ($lssMode) {
            case ShipLSSModeEnum::LSS_NORMAL:
                return _("Territorialansicht deaktivieren");
            case ShipLSSModeEnum::LSS_BORDER:
                return _("Territorialansicht aktivieren");
        }
        return '';
    }
}
