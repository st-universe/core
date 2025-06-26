<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;

interface CheckDestinationInterface
{
    public function validate(
        Spacecraft $spacecraft,
        int $posx,
        int $posy
    ): Map|StarSystemMap;
}
