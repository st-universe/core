<?php

declare(strict_types=1);

namespace Stu\Component\Station;

enum StationLocationEnum: string
{
    // where can the station type be build
    case BUILDABLE_EVERYWHERE = 'überall';
    case BUILDABLE_OVER_SYSTEM = 'über einem System';
    case BUILDABLE_INSIDE_SYSTEM = 'innerhalb eines Systems';
    case BUILDABLE_OUTSIDE_SYSTEM = 'außerhalb eines Systems';
}
