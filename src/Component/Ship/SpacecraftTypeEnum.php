<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

enum SpacecraftTypeEnum: int
{
    case SPACECRAFT_TYPE_SHIP = 0;
    case SPACECRAFT_TYPE_STATION = 1;
    case SPACECRAFT_TYPE_OTHER = 2;
}
