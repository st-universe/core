<?php

namespace Stu\Component\Spacecraft\System\Utility;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(
        SpacecraftWrapperInterface $wrapper,
        Ship $tractoredShip,
        InformationInterface $information
    ): bool;

    public function isTractorSystemStressed(
        SpacecraftWrapperInterface $wrapper,
        Ship $tractoredShip
    ): bool;

    public function stressTractorSystemForTowing(
        SpacecraftWrapperInterface $wrapper,
        Ship $tractoredShip,
        MessageCollectionInterface $messages
    ): void;
}
