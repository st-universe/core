<?php

namespace Stu\Module\Station\View\ShowStation;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\StationInterface;

class ConstructionProgressWrapper
{
    public function __construct(
        private ConstructionProgressInterface $progress,
        private StationInterface $station,
        private int $dockedWorbeeCount,
        private int $neededWorbeeCount
    ) {}

    public function isUnderConstruction(): bool
    {
        return $this->station->getState() == SpacecraftStateEnum::UNDER_CONSTRUCTION;
    }

    public function isScrapped(): bool
    {
        return $this->station->getState() == SpacecraftStateEnum::UNDER_SCRAPPING;
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
