<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;

/**
 * Stops a ship's emergency call
 */
final class StopEmergency implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STOP_EMERGENCY';

    private ShipLoaderInterface $shipLoader;

    private ShipStateChangerInterface $shipStateChanger;

    private StopEmergencyRequestInterface $stopEmergencyRequest;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStateChangerInterface $shipStateChanger,
        StopEmergencyRequestInterface $stopEmergencyRequest
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStateChanger = $shipStateChanger;
        $this->stopEmergencyRequest = $stopEmergencyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $this->stopEmergencyRequest->getShipId(),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        if (!$ship->isInEmergency()) {
            return;
        }

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        $game->addInformation('Das Notrufsignal wurde beendet');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
