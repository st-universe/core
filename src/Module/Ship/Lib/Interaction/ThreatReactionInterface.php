<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ThreatReactionInterface
{
    public function reactToThreat(
        ShipWrapperInterface $ship,
        ShipWrapperInterface $target,
        ShipInteractionEnum $interaction
    ): bool;
}
