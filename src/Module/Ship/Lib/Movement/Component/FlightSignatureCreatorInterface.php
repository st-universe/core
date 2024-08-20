<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;

interface FlightSignatureCreatorInterface
{
    /**
     * Create signature for flight paths
     */
    public function createSignatures(
        ShipInterface $ship,
        int $flightMethod,
        LocationInterface $currentField,
        LocationInterface $nextField
    ): void;
}
