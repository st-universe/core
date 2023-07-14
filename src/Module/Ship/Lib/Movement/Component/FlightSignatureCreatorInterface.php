<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface FlightSignatureCreatorInterface
{
    /**
     * Create signature for flight paths
     */
    public function createSignatures(
        ShipInterface $ship,
        int $flightMethod,
        MapInterface|StarSystemMapInterface $currentField,
        MapInterface|StarSystemMapInterface $nextField
    ): void;
}
