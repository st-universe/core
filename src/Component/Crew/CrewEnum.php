<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

final class CrewEnum
{

    public const CREW_TYPE_COMMAND = 1;
    public const CREW_TYPE_SECURITY = 2;
    public const CREW_TYPE_SCIENCE = 3;
    public const CREW_TYPE_TECHNICAL = 4;
    public const CREW_TYPE_NAVIGATION = 5;
    public const CREW_TYPE_CREWMAN = 6;
    public const CREW_TYPE_CAPTAIN = 7;
    public const CREW_GENDER_MALE = 1;
    public const CREW_GENDER_FEMALE = 2;
    public const CREW_TYPE_FIRST = CrewEnum::CREW_TYPE_COMMAND;
    public const CREW_TYPE_LAST = CrewEnum::CREW_TYPE_CAPTAIN;
}
