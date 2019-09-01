<?php

namespace Stu\Module\Tick\Ship;

use ShipData;

interface ShipTickInterface
{
    public function work(ShipData $ship): void;
}