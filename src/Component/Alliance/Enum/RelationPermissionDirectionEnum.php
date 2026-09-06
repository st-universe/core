<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Enum;

enum RelationPermissionDirectionEnum: int
{
    case MUTUAL = 1;
    case SOURCE_TO_RECIPIENT = 2;
}
