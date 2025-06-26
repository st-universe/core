<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Colony;

interface ManagerProviderFactoryInterface
{
    public function getManagerProviderColony(Colony $colony): ManagerProviderInterface;
    public function getManagerProviderStation(StationWrapperInterface $wrapper): ManagerProviderInterface;
}
