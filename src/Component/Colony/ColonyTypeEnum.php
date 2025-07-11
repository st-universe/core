<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

enum ColonyTypeEnum: int
{
    case PLANET = 1;
    case MOON = 2;
    case ASTEROID = 3;
}
