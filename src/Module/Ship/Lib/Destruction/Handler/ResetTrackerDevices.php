<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class ResetTrackerDevices implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private ShipSystemManagerInterface $shipSystemManager,
        private ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $shipId = $destroyedShipWrapper->get()->getId();

        foreach ($this->shipSystemRepository->getTrackingShipSystems($shipId) as $system) {
            $wrapper = $this->shipWrapperFactory->wrapShip($system->getShip());

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);
        }
    }
}
