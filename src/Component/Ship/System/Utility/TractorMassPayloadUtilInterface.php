<?php

namespace Stu\Component\Ship\System\Utility;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        InformationInterface $information
    ): bool;

    public function isTractorSystemStressed(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): bool;

    public function stressTractorSystemForTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        MessageCollectionInterface $messages
    ): void;
}
