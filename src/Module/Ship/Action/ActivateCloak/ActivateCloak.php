<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateCloak;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_CLOAK';

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

        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->shieldIsActive()) {
            $ship->setShieldState(0);
            $game->addInformation("Schilde deaktiviert");
        }
        if ($ship->isDocked()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        $ship->setCloak(1);
        $ship->save();
        $game->addInformation("Tarnung aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
