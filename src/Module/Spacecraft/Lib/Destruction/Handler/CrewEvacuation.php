<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class CrewEvacuation implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private SpacecraftLeaverInterface $spacecraftLeaver
    ) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        //leave ship if there is crew
        if ($destroyedSpacecraftWrapper->get()->getCrewCount() > 0) {
            $msg = $this->spacecraftLeaver->evacuate($destroyedSpacecraftWrapper);

            $informations->addInformation($msg);
        }
    }
}
