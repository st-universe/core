<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateShields;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

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

        if ($ship->cloakIsActive()) {
            $game->addInformation("Die Tarnung ist aktiviert");
            return;
        }
        if ($ship->isTraktorbeamActive()) {
            $game->addInformation(_("Der Traktorstrahl ist aktiviert"));
            return;
        }
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->getShield() <= 1) {
            $game->addInformation("Schilde sind nicht aufgeladen");
            return;
        }
        $ship->cancelRepair();
        if ($ship->isDocked()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        $ship->setShieldState(1);
        $ship->save();
        $game->addInformation("Schilde aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
