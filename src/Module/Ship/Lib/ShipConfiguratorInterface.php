<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Lib\Map\Location;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface ShipConfiguratorInterface
{
    public function setLocation(MapInterface|StarSystemMapInterface|Location $location): ShipConfiguratorInterface;

    public function loadEps(int $percentage): ShipConfiguratorInterface;

    public function loadBattery(int $percentage): ShipConfiguratorInterface;

    public function loadReactor(int $percentage): ShipConfiguratorInterface;

    public function loadWarpdrive(int $percentage): ShipConfiguratorInterface;

    public function createCrew(): ShipConfiguratorInterface;

    public function setAlertState(ShipAlertStateEnum $alertState): ShipConfiguratorInterface;

    public function setTorpedo(?int $torpedoTypeId = null): ShipConfiguratorInterface;

    public function maxOutSystems(): ShipConfiguratorInterface;

    public function setShipName(string $name): ShipConfiguratorInterface;

    public function finishConfiguration(): ShipWrapperInterface;
}
