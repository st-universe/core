<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;

interface CancelColonyBlockOrDefendInterface
{
    public function work(
        ShipInterface $ship,
        InformationWrapper $informations,
        bool $isTraktor = false
    ): void;
}
