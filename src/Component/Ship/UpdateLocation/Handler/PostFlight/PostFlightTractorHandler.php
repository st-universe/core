<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PostFlight;

use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class PostFlightTractorHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    public function __construct(
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend
    ) {
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
    }

    public function handle(ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip): void
    {
        $ship = $wrapper->get();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip === null) {
            return;
        }

        //check for tractor system health
        $informations = new InformationWrapper();
        $this->tractorMassPayloadUtil->tractorSystemSurvivedTowing($wrapper, $tractoredShip, $informations);

        // colony stuff
        $this->cancelColonyBlockOrDefend->work($ship, $informations, true);
        $this->addMessagesInternal($informations->getInformations());
    }
}
