<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftTickHandlerInterface
{
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void;
}
