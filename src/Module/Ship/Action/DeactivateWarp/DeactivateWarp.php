<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateWarp;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateWarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_WARP';

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

        if (!$ship->getWarpState()) {
            return;
        }
        if ($ship->traktorBeamFromShip()) {
            $ship->getTraktorShip()->setWarpState(0);
            $ship->getTraktorShip()->save();
        }
        // @todo Alarm Rot
        $ship->setWarpState(0);
        $ship->save();
        $game->addInformation("Die " . $ship->getName() . " hat den Warpantrieb deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
