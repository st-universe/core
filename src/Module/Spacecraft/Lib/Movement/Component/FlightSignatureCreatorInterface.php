<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface FlightSignatureCreatorInterface
{
    /**
     * Create signature for flight paths
     */
    public function createSignatures(
        SpacecraftInterface $spacecraft,
        DirectionEnum $direction,
        LocationInterface $currentField,
        LocationInterface $nextField
    ): void;
}
