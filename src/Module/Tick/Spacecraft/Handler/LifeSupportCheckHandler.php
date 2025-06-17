<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class LifeSupportCheckHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private readonly CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private readonly SpacecraftSystemRepositoryInterface $spacecraftSystemRepository,
        private readonly SpacecraftLeaverInterface $spacecraftLeaver
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        $spacecraft = $wrapper->get();

        // leave spacecraft
        if (
            $this->crewAssignmentRepository->getAmountBySpacecraft($spacecraft) > 0
            && !$this->spacecraftSystemRepository->isSystemHealthy($spacecraft, SpacecraftSystemTypeEnum::LIFE_SUPPORT)
        ) {
            $information->addInformation('Die Lebenserhaltung ist ausgefallen:');
            $information->addInformation($this->spacecraftLeaver->evacuate($wrapper));

            throw new SpacecraftTickFinishedException();
        }
    }
}
