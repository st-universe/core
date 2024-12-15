<?php

namespace Stu\Component\Spacecraft\System\Utility;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        InformationInterface $information
    ): bool;

    public function isTractorSystemStressed(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): bool;

    public function stressTractorSystemForTowing(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        MessageCollectionInterface $messages
    ): void;
}
