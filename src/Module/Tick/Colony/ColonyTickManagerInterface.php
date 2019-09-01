<?php

namespace Stu\Module\Tick\Colony;

interface ColonyTickManagerInterface
{
    public function work(int $tickId): void;
}