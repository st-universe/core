<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Entity\ColonyInterface;

interface ColonyTickComponentInterface
{

    /**
     * @param array<int, ColonyProduction> $production
     */
    public function work(ColonyInterface $colony, array &$production): void;
}
