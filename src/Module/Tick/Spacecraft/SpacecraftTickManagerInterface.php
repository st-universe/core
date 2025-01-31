<?php

namespace Stu\Module\Tick\Spacecraft;

interface SpacecraftTickManagerInterface
{
    public function work(bool $doCommit = false): void;
}
