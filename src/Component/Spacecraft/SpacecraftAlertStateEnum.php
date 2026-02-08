<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use RuntimeException;

enum SpacecraftAlertStateEnum: int
{
    case ALERT_GREEN = 1;
    case ALERT_YELLOW = 2;
    case ALERT_RED = 3;

    public function getDescription(): string
    {
        return match ($this) {
            self::ALERT_GREEN => "Alarm Grün",
            self::ALERT_YELLOW => "Alarm Gelb",
            self::ALERT_RED => "Alarm Rot"
        };
    }

    public function getEpsUsage(): int
    {
        return match ($this) {
            self::ALERT_GREEN => 0,
            self::ALERT_YELLOW => 1,
            self::ALERT_RED => 2
        };
    }

    public function isAtLeast(SpacecraftAlertStateEnum $minimum): bool
    {
        return $this->value >= $minimum->value;
    }

    public static function getRandomAlertLevel(): SpacecraftAlertStateEnum
    {
        /** @var array<int> */
        $values = array_map(fn (SpacecraftAlertStateEnum $alertState) => $alertState->value, self::cases());
        if ($values === []) {
            throw new RuntimeException('No alert states defined');
        }

        return self::from($values[array_rand($values)]);
    }
}
