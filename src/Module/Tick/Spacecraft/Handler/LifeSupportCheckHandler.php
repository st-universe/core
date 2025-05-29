<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;

class LifeSupportCheckHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private SpacecraftLeaverInterface $spacecraftLeaver
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        $spacecraft = $wrapper->get();

        // leave spacecraft
        if (
            $spacecraft->getCrewCount() > 0
            && !$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::LIFE_SUPPORT)
        ) {
            $information->addInformation('Die Lebenserhaltung ist ausgefallen:');
            $information->addInformation($this->spacecraftLeaver->evacuate($wrapper));

            throw new SpacecraftTickFinishedException();
        }
    }
}
