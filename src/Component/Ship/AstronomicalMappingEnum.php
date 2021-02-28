<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class AstronomicalMappingEnum
{
    //states
    public const NONE = -1;
    public const PLANNABLE = 0;
    public const PLANNED = 1;
    public const MEASURED = 2;
    public const FINISHING = 3;
    public const DONE = 4;

    //other
    public const MEASUREMENT_COUNT = 5;
    public const TURNS_TO_FINISH = 3;
}
