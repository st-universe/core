<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class CancelTakeover implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipTakeoverManagerInterface $shipTakeoverManager
    ) {
    }

    #[Override]
    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $this->shipTakeoverManager->cancelBothTakeover(
            $destroyedShipWrapper->get(),
            ', da das Schiff zerstört wurde'
        );
    }
}
