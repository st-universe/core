<?php

namespace Stu\Module\Station\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\Creation\SpecialSystemsProviderInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ConstructionProgressModuleInterface;
use Stu\Orm\Entity\ModuleInterface;

class StationSpecialSystemsProvider implements SpecialSystemsProviderInterface
{
    public function __construct(private ConstructionProgressInterface $progress) {}

    public function getSpecialSystemModules(): Collection
    {
        return $this->progress
            ->getSpecialModules()
            ->map(fn(ConstructionProgressModuleInterface $progressModule): ModuleInterface => $progressModule->getModule());
    }
}
