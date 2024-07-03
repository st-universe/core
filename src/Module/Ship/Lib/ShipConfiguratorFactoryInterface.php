<?php

namespace Stu\Module\Ship\Lib;

interface ShipConfiguratorFactoryInterface
{
    public function createShipConfigurator(ShipWrapperInterface $wrapper): ShipConfiguratorInterface;
}
