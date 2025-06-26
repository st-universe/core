<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Spacecraft;

interface CancelColonyBlockOrDefendInterface
{
    public function work(
        Spacecraft $spacecraft,
        InformationInterface $informations,
        bool $isTraktor = false
    ): void;
}
