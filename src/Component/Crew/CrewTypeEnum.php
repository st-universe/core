<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

enum CrewTypeEnum: int
{
    case COMMAND = 1;
    case SECURITY = 2;
    case SCIENCE = 3;
    case TECHNICAL = 4;
    case NAVIGATION = 5;
    case CREWMAN = 6;
    case CAPTAIN = 7;

    /**
     * @return array<CrewTypeEnum>
     */
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

    public function getFightCapability(): int
    {
        return match ($this) {
            self::CAPTAIN => 10,
            self::COMMAND => 8,
            self::SECURITY => 20,
            self::SCIENCE => 0,
            self::TECHNICAL => 4,
            self::NAVIGATION => 2,
            self::CREWMAN => 6
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::CAPTAIN => _("Captain"),
            self::COMMAND => _("Commander"),
            self::SECURITY => _("Sicherheit"),
            self::SCIENCE => _("Wissenschaftler"),
            self::TECHNICAL => _("Ingenieur"),
            self::NAVIGATION => _("Navigator"),
            self::CREWMAN => _("Crewman")
        };
    }
}
