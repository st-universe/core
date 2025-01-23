<?php

namespace Stu\Module\Station\Lib;

use Stu\Component\Spacecraft\System\Data\AggregationSystemSystemData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StationInterface;

interface StationWrapperInterface extends SpacecraftWrapperInterface
{
    public function get(): StationInterface;

    public function canBeScrapped(): bool;

    public function getAggregationSystemSystemData(): ?AggregationSystemSystemData;
}
