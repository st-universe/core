<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface FlightSignatureCreatorInterface
{
    /**
     * Create signature for inner-system flight paths
     */
    public function createInnerSystemSignatures(
        ShipInterface $ship,
        int $flightMethod,
        StarSystemMapInterface $currentField,
        StarSystemMapInterface $nextField
    ): void;

    /**
     * Create signature for outer-system flight paths
     */
    public function createOuterSystemSignatures(
        ShipInterface $ship,
        int $flightDirection,
        MapInterface $currentField,
        MapInterface $nextField
    ): void;
}
