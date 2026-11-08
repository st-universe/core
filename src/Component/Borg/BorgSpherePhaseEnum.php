<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

enum BorgSpherePhaseEnum: string
{
    case APPROACH = 'approach';
    case ROAMING = 'roaming';
    case REGENERATING = 'regenerating';
    case ESCAPED = 'escaped';
    case DESTROYED = 'destroyed';
}
