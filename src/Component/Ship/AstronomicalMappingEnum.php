<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

final class AstronomicalMappingEnum
{
    //states
    public const int NONE = -1;
    public const int PLANNABLE = 0;
    public const int PLANNED = 1;
    public const int MEASURED = 2;
    public const int FINISHING = 3;
    public const int DONE = 4;

    //other
    public const int MEASUREMENT_COUNT = 5;
    public const int TURNS_TO_FINISH = 3;
}
