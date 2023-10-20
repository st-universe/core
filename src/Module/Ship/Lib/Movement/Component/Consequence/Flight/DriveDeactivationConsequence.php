<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class DriveDeactivationConsequence extends AbstractFlightConsequence
{
    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(ShipSystemManagerInterface $shipSystemManager)
    {
        $this->shipSystemManager = $shipSystemManager;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if ($wrapper->get()->isTractored()) {
            return;
        }

        if (!$flightRoute->isWarpDriveNeeded()) {
            $this->deactivateSystem(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
                $messages
            );
        }

        if (!$flightRoute->isImpulseDriveNeeded()) {
            $this->deactivateSystem(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
                $messages
            );
        }
    }

    private function deactivateSystem(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeEnum $systemId,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        if (!$ship->hasShipSystem($systemId)) {
            return;
        }

        if (!$ship->getSystemState($systemId)) {
            return;
        }

        $message = new Message();
        $messages->add($message);

        $this->shipSystemManager->deactivate($wrapper, $systemId, true);
        $message->add(sprintf(
            _('Die %s deaktiviert %s %s'),
            $ship->getName(),
            $systemId === ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL ? 'die' : 'den',
            ShipSystemTypeEnum::getDescription($systemId)
        ));
    }
}
