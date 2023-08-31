<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetWarpSplit;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;


final class FleetWarpSplit implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_WARP_SPLIT';

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->helper->setWarpSplitFleet(request::indInt('id'), $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
