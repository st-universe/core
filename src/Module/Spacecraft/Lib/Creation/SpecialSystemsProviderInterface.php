<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ModuleInterface;

interface SpecialSystemsProviderInterface
{
    /**
     * @return Collection<int, ModuleInterface>
     */
    public function getSpecialSystemModules(): Collection;
}
