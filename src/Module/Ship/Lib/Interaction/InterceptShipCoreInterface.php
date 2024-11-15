<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface InterceptShipCoreInterface
{
    public function intercept(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        InformationInterface $informations
    ): void;
}
