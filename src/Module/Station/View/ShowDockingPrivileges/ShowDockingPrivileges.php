<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingPrivileges;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\Lib\DockingPrivilegeItem;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;

final class ShowDockingPrivileges implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_LIST';

    public function __construct(private StationUiFactoryInterface $stationUiFactory, private StationLoaderInterface $stationLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->showMacro('html/station/dockPrivileges.twig');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'DOCKING_PRIVILEGES',
            $ship->getDockPrivileges()->map(
                fn(DockingPrivilegeInterface $dockingPrivilege): DockingPrivilegeItem =>
                $this->stationUiFactory->createDockingPrivilegeItem($dockingPrivilege)
            )
        );
    }
}
