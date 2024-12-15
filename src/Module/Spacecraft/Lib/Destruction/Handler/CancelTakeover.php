<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class CancelTakeover implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private ShipTakeoverManagerInterface $shipTakeoverManager
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();

        $this->shipTakeoverManager->cancelBothTakeover(
            $spacecraft,
            sprintf(
                ', da das %s zerstört wurde',
                $spacecraft->getType()->getDescription()
            )
        );
    }
}
