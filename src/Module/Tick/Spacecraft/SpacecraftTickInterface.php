<?php

namespace Stu\Module\Tick\Spacecraft;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftTickInterface
{
    public function workSpacecraft(SpacecraftWrapperInterface $wrapper): void;
}
