<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

enum SpacecraftAlertStateEnum: int
{
    case ALERT_GREEN = 1;
    case ALERT_YELLOW = 2;
    case ALERT_RED = 3;

    public function getDescription(): string
    {
        return match ($this) {
            SpacecraftAlertStateEnum::ALERT_GREEN => _("Alarm Grün"),
            SpacecraftAlertStateEnum::ALERT_YELLOW => _("Alarm Gelb"),
            SpacecraftAlertStateEnum::ALERT_RED => _("Alarm Rot")
        };
    }

    public function isAtLeast(SpacecraftAlertStateEnum $minimum): bool
    {
        return $this->value >= $minimum->value;
    }

    public static function getRandomAlertLevel(): SpacecraftAlertStateEnum
    {
        /** @var array<int> */
        $values = array_map(fn(SpacecraftAlertStateEnum $alertState) => $alertState->value, self::cases());

        return self::from($values[array_rand($values)]);
    }
}
