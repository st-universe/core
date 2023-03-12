<?php

namespace Stu\Module\Tick\Colony;

use Stu\Orm\Entity\ColonyInterface;

interface ColonyTickInterface
{
    public function work(ColonyInterface $colony, array $commodityArray): void;
}
