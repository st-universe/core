<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use RuntimeException;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;

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

    public function isAtLeast(ShipAlertStateEnum $minimum): bool
    {
        return $this->value >= $minimum->value;
    }

    public static function getRandomAlertLevel(): ShipAlertStateEnum
    {
        /** @var array<int> */
        $values = array_map(fn (ShipAlertStateEnum $alertState) => $alertState->value, self::cases());

        return self::from($values[array_rand($values)]);
    }

    public function getAttackCause(): ShipAttackCauseEnum
    {
        return match ($this) {
            ShipAlertStateEnum::ALERT_GREEN => throw new RuntimeException('this should not happen'),
            ShipAlertStateEnum::ALERT_YELLOW => ShipAttackCauseEnum::ALERT_YELLOW,
            ShipAlertStateEnum::ALERT_RED => ShipAttackCauseEnum::ALERT_RED
        };
    }
}
