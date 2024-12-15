<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftCreationConfigInterface
{
    public function getSpacecraft(): ?SpacecraftInterface;

    /** @return Collection<int, ModuleInterface> */
    public function getSpecialSystemModules(): Collection;
}
