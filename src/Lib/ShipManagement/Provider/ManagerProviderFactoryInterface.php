<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;

interface ManagerProviderFactoryInterface
{
    public function getManagerProviderColony(ColonyInterface $colony): ManagerProviderInterface;
    public function getManagerProviderStation(ShipWrapperInterface $wrapper): ManagerProviderInterface;
}
