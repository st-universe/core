<?php

namespace Stu\Module\Tick\Colony;

use ColonyData;

interface ColonyTickInterface
{
    public function work(ColonyData $colony): void;
}