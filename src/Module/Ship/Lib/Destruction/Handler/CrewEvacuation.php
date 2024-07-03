<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Crew\ShipLeaverInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class CrewEvacuation implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipLeaverInterface $shipLeaver
    ) {
    }

    #[Override]
    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        //leave ship if there is crew
        if ($destroyedShipWrapper->get()->getCrewCount() > 0) {
            $msg = $this->shipLeaver->evacuate($destroyedShipWrapper);

            $informations->addInformation($msg);
        }
    }
}
