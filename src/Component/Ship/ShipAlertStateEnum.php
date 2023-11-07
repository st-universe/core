<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

enum ShipAlertStateEnum: int
{
    case ALERT_GREEN = 1;
    case ALERT_YELLOW = 2;
    case ALERT_RED = 3;

    public static function getDescription(ShipAlertStateEnum $alertState): string
    {
        switch ($alertState) {
            case ShipAlertStateEnum::ALERT_GREEN:
                return _("Alarm Grün");
            case ShipAlertStateEnum::ALERT_YELLOW:
                return _("Alarm Gelb");
            case ShipAlertStateEnum::ALERT_RED:
                return _("Alarm Rot");
        }
    }
}
