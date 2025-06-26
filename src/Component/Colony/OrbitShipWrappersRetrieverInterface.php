<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftGroupInterface;
use Stu\Orm\Entity\Colony;

interface OrbitShipWrappersRetrieverInterface
{
    /**
     * @return Collection<string, SpacecraftGroupInterface>
     */
    public function retrieve(Colony $colony): Collection;
}
