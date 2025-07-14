<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

enum AstronomicalMappingStateEnum: int
{
    case NONE = -1;
    case PLANNABLE = 0;
    case PLANNED = 1;
    case MEASURED = 2;
    case FINISHING = 3;
    case DONE = 4;
}
