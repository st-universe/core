<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StopEmergency;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;

final class StopEmergency implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STOP_EMERGENCY';

    private ShipLoaderInterface $shipLoader;

    private ShipStateChangerInterface $shipStateChanger;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStateChangerInterface $shipStateChanger
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStateChanger = $shipStateChanger;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();

        if (!$ship->isInEmergency()) {
            return;
        }

        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        $game->addInformation(_("Das Notrufsignal wurde beendet"));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
