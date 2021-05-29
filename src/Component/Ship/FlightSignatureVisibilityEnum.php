<?php

declare(strict_types=1);

namespace Stu\Component\Ship;


final class FlightSignatureVisibilityEnum
{
    //ship name info
    public const NAME_VISIBILITY_CLOAKED = 0;
    public const NAME_VISIBILITY_UNCLOAKED = 43200; //12 hours

    //ship rump info
    public const RUMP_VISIBILITY_CLOAKED = 43200; //12 hours
    public const RUMP_VISIBILITY_UNCLOAKED = 86400; //24 hours

    //ship signature info
    public const SIG_VISIBILITY_CLOAKED = 64800; //18 hours
    public const SIG_VISIBILITY_UNCLOAKED = 172800; //48 hours
}
