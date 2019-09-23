<?php

namespace Stu\Module\Tick\Ship;

use Stu\Orm\Entity\ShipInterface;

interface ShipTickInterface
{
    public function work(ShipInterface $ship): void;
}