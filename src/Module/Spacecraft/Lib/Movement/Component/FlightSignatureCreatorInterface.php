<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

interface FlightSignatureCreatorInterface
{
    /**
     * Create signature for flight paths
     */
    public function createSignatures(
        Spacecraft $spacecraft,
        DirectionEnum $direction,
        Location $currentField,
        Location $nextField
    ): void;
}
