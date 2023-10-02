<?php

namespace Stu\Component\Ship\System\Utility;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface TractorMassPayloadUtilInterface
{
    public function tryToTow(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): ?string;

    public function stressTractorSystemForTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        FightMessageCollectionInterface $messages
    ): void;
}
