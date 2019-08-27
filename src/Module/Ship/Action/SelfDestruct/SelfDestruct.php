<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestruct;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SelfDestruct implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

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

        $code = request::postString('destructioncode');

        // @todo repair
        return;

        //$ship->selfDestroy();
        //DB()->commitTransaction();
        //$game->redirectTo('ship.php?B_SELFDESTRUCT=1&sstr=' . $this->getSessionString());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
