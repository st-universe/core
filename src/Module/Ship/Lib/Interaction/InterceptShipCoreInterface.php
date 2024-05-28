<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\ShipInterface;

interface InterceptShipCoreInterface
{
    public function intercept(ShipInterface $ship, ShipInterface $target, InformationInterface $informations): void;
}
