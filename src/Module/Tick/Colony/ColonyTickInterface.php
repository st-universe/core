<?php

namespace Stu\Module\Tick\Colony;

use Stu\Orm\Entity\Colony;

interface ColonyTickInterface
{
    public function work(Colony $colony): void;
}
