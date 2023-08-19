<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetWarpSplit;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;


final class FleetWarpSplit implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_WARP_SPLIT';

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader
    ) {
        $this->helper = $helper;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->helper->setWarpSplitFleet(request::indInt('id'), $game);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
