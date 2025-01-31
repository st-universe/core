<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

enum IonStormMovementType: int
{
    case STATIC = 1;
    case VARIABLE = 2;
}
