<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

enum CrewTypeEnum: int
{
    case CAPTAIN = 1;
    case COMMAND = 2;
    case TACTIC = 3;
    case SCIENCE = 4;
    case TECHNICAL = 5;
    case NAVIGATION = 6;
    case CREWMAN = 7;

    /**
     * @return array<CrewTypeEnum>
     */
    public static function getOrder(): array
    {
        return [
            self::CAPTAIN,
            self::COMMAND,
            self::TACTIC,
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
            self::TACTIC => 20,
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
            self::TACTIC => _("Taktik"),
            self::SCIENCE => _("Wissenschaftler"),
            self::TECHNICAL => _("Ingenieur"),
            self::NAVIGATION => _("Navigator"),
            self::CREWMAN => _("Crewman")
        };
    }
}
