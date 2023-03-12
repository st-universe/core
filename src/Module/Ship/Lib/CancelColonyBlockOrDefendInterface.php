<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface CancelColonyBlockOrDefendInterface
{
    public function work(ShipInterface $ship, bool $isTraktor = false): array;
}
