<?php

namespace Stu\Module\Station\Lib;

use Stu\Component\Spacecraft\System\Data\AggregationSystemSystemData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Station;

interface StationWrapperInterface extends SpacecraftWrapperInterface
{
    #[\Override]
    public function get(): Station;

    public function canBeScrapped(): bool;

    public function getAggregationSystemSystemData(): ?AggregationSystemSystemData;
}
