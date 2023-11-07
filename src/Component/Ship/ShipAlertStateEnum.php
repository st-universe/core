<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

enum ShipAlertStateEnum: int
{
    case ALERT_GREEN = 1;
    case ALERT_YELLOW = 2;
    case ALERT_RED = 3;

    public function getDescription(): string
    {
        return match ($this) {
            ShipAlertStateEnum::ALERT_GREEN => _("Alarm Grün"),
            ShipAlertStateEnum::ALERT_YELLOW => _("Alarm Gelb"),
            ShipAlertStateEnum::ALERT_RED => _("Alarm Rot")
        };
    }
}
