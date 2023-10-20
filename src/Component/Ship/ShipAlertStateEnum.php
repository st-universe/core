<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class ShipAlertStateEnum
{
    // alert states
    public const ALERT_GREEN = 1;
    public const ALERT_YELLOW = 2;
    public const ALERT_RED = 3;

    public static function getDescription(int $alertState): string
    {
        switch ($alertState) {
            case ShipAlertStateEnum::ALERT_GREEN:
                return _("Alarm Grün");
            case ShipAlertStateEnum::ALERT_YELLOW:
                return _("Alarm Gelb");
            case ShipAlertStateEnum::ALERT_RED:
                return _("Alarm Rot");
        }
        return '';
    }
}
