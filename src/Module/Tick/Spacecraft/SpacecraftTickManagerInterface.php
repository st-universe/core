<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

interface SpacecraftTickManagerInterface
{
    public function work(bool $doCommit = false): void;
}
