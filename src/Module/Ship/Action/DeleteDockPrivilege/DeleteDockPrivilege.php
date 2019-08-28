<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeleteDockPrivilege;

use DockingRights;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeleteDockPrivilege implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_DOCKPRIVILEGE';

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

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        $privilegeId = request::getIntFatal('privilegeid');
        $privilege = new DockingRights($privilegeId);
        if ($privilege->getShipId() != $ship->getId()) {
            return;
        }
        $privilege->deleteFromDatabase();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
