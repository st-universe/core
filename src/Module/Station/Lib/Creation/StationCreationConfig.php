<?php

namespace Stu\Module\Station\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreationConfigInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ConstructionProgressModuleInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class StationCreationConfig implements SpacecraftCreationConfigInterface
{
    public function __construct(private ConstructionProgressInterface $progress) {}

    #[Override]
    public function getSpacecraft(): ?SpacecraftInterface
    {
        return $this->progress->getStation();
    }

    #[Override]
    public function getSpecialSystemModules(): Collection
    {
        return $this->progress
            ->getSpecialModules()
            ->map(fn(ConstructionProgressModuleInterface $progressModule): ModuleInterface => $progressModule->getModule());
    }
}
