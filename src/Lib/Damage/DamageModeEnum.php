<?php

declare(strict_types=1);

namespace Stu\Lib\Damage;

enum DamageModeEnum: int
{
    case HULL = 1;
    case SHIELDS = 2;
}
