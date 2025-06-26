<?php

namespace Stu\Module\Station\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreationConfigInterface;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\ConstructionProgressModule;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Spacecraft;

class StationCreationConfig implements SpacecraftCreationConfigInterface
{
    public function __construct(private ConstructionProgress $progress) {}

    #[Override]
    public function getSpacecraft(): ?Spacecraft
    {
        return $this->progress->getStation();
    }

    #[Override]
    public function getSpecialSystemModules(): Collection
    {
        return $this->progress
            ->getSpecialModules()
            ->map(fn(ConstructionProgressModule $progressModule): Module => $progressModule->getModule());
    }
}
