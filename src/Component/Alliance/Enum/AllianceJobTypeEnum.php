<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

enum AllianceJobTypeEnum: int
{
    case FOUNDER = 1;
    case SUCCESSOR = 2;
    case DIPLOMATIC = 3;
    case PENDING = 4;
}
