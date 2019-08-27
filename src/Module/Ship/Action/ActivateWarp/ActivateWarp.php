<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateWarp;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class ActivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_WARP';

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

        if ($ship->getWarpState()) {
            return;
        }
        if (!$ship->isWarpAble()) {
            return;
        }
        // @todo arpantrieb beschädigt
        $wrapper = new SystemActivationWrapper($ship);
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->isDocked()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        if ($ship->traktorBeamFromShip()) {
            if ($ship->getEps() == 0) {
                $game->addInformation("Der Traktorstrahl zur " . $ship->getTraktorShip()->getName() . " wurde aufgrund von Energiemangel deaktiviert");
                $ship->getTraktorShip()->unsetTraktor();
                $ship->getTraktorShip()->save();
                $ship->unsetTraktor();
            } else {
                $ship->getTraktorShip()->setWarpState(1);
                $ship->getTraktorShip()->save();
                $ship->lowerEps(1);
            }
        }
        $ship->setWarpState(1);
        $ship->save();
        $game->addInformation("Die " . $ship->getName() . " hat den Warpantrieb aktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
