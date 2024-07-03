<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\ShipInterface;

interface CancelColonyBlockOrDefendInterface
{
    public function work(
        ShipInterface $ship,
        InformationInterface $informations,
        bool $isTraktor = false
    ): void;
}
