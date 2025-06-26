<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Colony;

interface ColonyTickComponentInterface
{
    /**
     * @param array<int, ColonyProduction> $production
     */
    public function work(Colony $colony, array &$production, InformationInterface $information): void;
}
