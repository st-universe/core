<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\DeleteDockPrivilege;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowDockingPrivileges\ShowDockingPrivileges;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;

final class DeleteDockPrivilege implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_DOCKPRIVILEGE';

    public function __construct(private StationLoaderInterface $stationLoader, private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->setView(ShowDockingPrivileges::VIEW_IDENTIFIER);
        $privilege = $this->dockingPrivilegeRepository->find(request::getIntFatal('privilegeid'));

        if (
            $privilege === null
            || $privilege->getStation() !== $station
        ) {
            return;
        }

        $this->dockingPrivilegeRepository->delete($privilege);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
