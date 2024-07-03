<?php

namespace Stu\Module\Ship\View\ShowShip;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ShipInterface;

class ConstructionProgressWrapper
{
    public function __construct(private ConstructionProgressInterface $progress, private ShipInterface $station, private int $dockedWorbeeCount, private int $neededWorbeeCount)
    {
    }

    public function isUnderConstruction(): bool
    {
        return $this->station->getState() == ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION;
    }

    public function isScrapped(): bool
    {
        return $this->station->getState() == ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING;
    }

    public function getRemainingTicks(): int
    {
        return $this->progress->getRemainingTicks();
    }

    public function getDockedWorkbeeCount(): int
    {
        return $this->dockedWorbeeCount;
    }

    public function getNeededWorkbeeCount(): int
    {
        return $this->neededWorbeeCount;
    }

    public function getWorkbeeColor(): string
    {
        return $this->dockedWorbeeCount < $this->neededWorbeeCount ? 'red' : 'green';
    }
}
