<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Component\Game\TimeConstants;

final class FlightSignatureVisibilityEnum
{
    //ship name info
    public const int NAME_VISIBILITY_CLOAKED = 0;
    public const int NAME_VISIBILITY_UNCLOAKED = 43200; //12 hours

    //ship rump info
    public const int RUMP_VISIBILITY_CLOAKED = 43200; //12 hours
    public const int RUMP_VISIBILITY_UNCLOAKED = TimeConstants::ONE_DAY_IN_SECONDS;

    //ship signature info
    public const int SIG_VISIBILITY_CLOAKED = 64800; //18 hours
    public const int SIG_VISIBILITY_UNCLOAKED = TimeConstants::TWO_DAYS_IN_SECONDS;
}
