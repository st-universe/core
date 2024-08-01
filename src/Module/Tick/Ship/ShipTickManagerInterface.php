<?php

namespace Stu\Module\Tick\Ship;

interface ShipTickManagerInterface
{
    public function work(bool $doCommit = false): void;
}
