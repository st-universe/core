<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

final class CrewEnum
{

    /**
     * @var int
     */
    public const CREW_TYPE_COMMAND = 1;

    /**
     * @var int
     */
    public const CREW_TYPE_SECURITY = 2;

    /**
     * @var int
     */
    public const CREW_TYPE_SCIENCE = 3;

    /**
     * @var int
     */
    public const CREW_TYPE_TECHNICAL = 4;

    /**
     * @var int
     */
    public const CREW_TYPE_NAVIGATION = 5;

    /**
     * @var int
     */
    public const CREW_TYPE_CREWMAN = 6;

    /**
     * @var int
     */
    public const CREW_TYPE_CAPTAIN = 7;

    /**
     * @var int
     */
    public const CREW_GENDER_MALE = 1;

    /**
     * @var int
     */
    public const CREW_GENDER_FEMALE = 2;

    /**
     * @var int[]
     */
    public const CREW_ORDER = [
        CrewEnum::CREW_TYPE_CAPTAIN,
        CrewEnum::CREW_TYPE_COMMAND,
        CrewEnum::CREW_TYPE_SECURITY,
        CrewEnum::CREW_TYPE_SCIENCE,
        CrewEnum::CREW_TYPE_TECHNICAL,
        CrewEnum::CREW_TYPE_NAVIGATION,
        CrewEnum::CREW_TYPE_CREWMAN
    ];

    public static function getDescription(?int $crewType): string
    {
        switch ($crewType) {
            case self::CREW_TYPE_CAPTAIN:
                return _("Captain");
            case self::CREW_TYPE_COMMAND:
                return _("Commander");
            case self::CREW_TYPE_SECURITY:
                return _("Sicherheit");
            case self::CREW_TYPE_SCIENCE:
                return _("Wissenschaftler");
            case self::CREW_TYPE_TECHNICAL:
                return _("Ingenieur");
            case self::CREW_TYPE_NAVIGATION:
                return _("Navigator");
            case self::CREW_TYPE_CREWMAN:
                return _("Crewman");
        }

        return '';
    }
}
