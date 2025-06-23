<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

enum CrewPositionEnum: int
{
    case COMMAND = 1;
    case SECURITY = 2;
    case SCIENCE = 3;
    case TECHNICAL = 4;
    case NAVIGATION = 5;
    case CREWMAN = 6;
    case CAPTAIN = 7;

    public function getDescription(): string
    {
        return match ($this) {
            self::CAPTAIN => "Captain",
            self::COMMAND => "Commander",
            self::SECURITY => "Sicherheit",
            self::SCIENCE => "Wissenschaftler",
            self::TECHNICAL => "Ingenieur",
            self::NAVIGATION => "Navigator",
            self::CREWMAN => "Crewman",
        };
    }

    public function getFightCapability(): int
    {
        return match ($this) {
            self::SECURITY => 20,
            self::CAPTAIN => 10,
            self::COMMAND => 8,
            self::CREWMAN => 6,
            self::TECHNICAL => 4,
            self::NAVIGATION => 2,
            self::SCIENCE => 0
        };
    }

    /** @return array<CrewPositionEnum> */
    public static function getOrder(): array
    {
        return [
            self::CAPTAIN,
            self::COMMAND,
            self::SECURITY,
            self::SCIENCE,
            self::TECHNICAL,
            self::NAVIGATION,
            self::CREWMAN
        ];
    }
}
