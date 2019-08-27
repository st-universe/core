<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SetYellowAlert;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SetYellowAlert implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_YELLOW_ALERT';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $ship->setAlertState(2);
        $ship->save();
        $game->addInformation("Die Alarmstufe wurde auf Gelb geändert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
