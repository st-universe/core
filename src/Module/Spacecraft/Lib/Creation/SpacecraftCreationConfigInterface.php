<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Spacecraft;

interface SpacecraftCreationConfigInterface
{
    public function getSpacecraft(): ?Spacecraft;

    /** @return Collection<int, Module> */
    public function getSpecialSystemModules(): Collection;
}
