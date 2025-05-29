<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;

class EpsSystemCheckHandler implements SpacecraftTickHandlerInterface
{
    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        if ($wrapper->getEpsSystemData() === null) {
            throw new SpacecraftTickFinishedException();
        }
    }
}
