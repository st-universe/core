<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

final class CrewEnum
{
    public const int CREW_TYPE_COMMAND = 1;

    public const int CREW_TYPE_SECURITY = 2;

    public const int CREW_TYPE_SCIENCE = 3;

    public const int CREW_TYPE_TECHNICAL = 4;

    public const int CREW_TYPE_NAVIGATION = 5;

    public const int CREW_TYPE_CREWMAN = 6;

    public const int CREW_TYPE_CAPTAIN = 7;

    public const int CREW_GENDER_MALE = 1;

    public const int CREW_GENDER_FEMALE = 2;

    /**
     * @var int[]
     */
    public const array CREW_ORDER = [
        CrewEnum::CREW_TYPE_CAPTAIN,
        CrewEnum::CREW_TYPE_COMMAND,
        CrewEnum::CREW_TYPE_SECURITY,
        CrewEnum::CREW_TYPE_SCIENCE,
        CrewEnum::CREW_TYPE_TECHNICAL,
        CrewEnum::CREW_TYPE_NAVIGATION,
        CrewEnum::CREW_TYPE_CREWMAN
    ];

    public const array CREW_FIGHT_CAPABILITIES = [
        CrewEnum::CREW_TYPE_CAPTAIN => 10,
        CrewEnum::CREW_TYPE_COMMAND => 8,
        CrewEnum::CREW_TYPE_SECURITY => 20,
        CrewEnum::CREW_TYPE_SCIENCE => 0,
        CrewEnum::CREW_TYPE_TECHNICAL => 4,
        CrewEnum::CREW_TYPE_NAVIGATION => 2,
        CrewEnum::CREW_TYPE_CREWMAN => 6
    ];

    public static function getDescription(?int $crewType): string
    {
        return match ($crewType) {
            self::CREW_TYPE_CAPTAIN => _("Captain"),
            self::CREW_TYPE_COMMAND => _("Commander"),
            self::CREW_TYPE_SECURITY => _("Sicherheit"),
            self::CREW_TYPE_SCIENCE => _("Wissenschaftler"),
            self::CREW_TYPE_TECHNICAL => _("Ingenieur"),
            self::CREW_TYPE_NAVIGATION => _("Navigator"),
            self::CREW_TYPE_CREWMAN => _("Crewman"),
            default => '',
        };
    }
}
