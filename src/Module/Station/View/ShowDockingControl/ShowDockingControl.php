<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingControl;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\Lib\DockingPrivilegeItem;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class ShowDockingControl implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_DOCK_CONTROL';

    public function __construct(private AllianceRepositoryInterface $allianceRepository, private StationUiFactoryInterface $stationUiFactory, private StationLoaderInterface $stationLoader) {}

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

        $game->setPageTitle(_('Dockkontrolle'));
        $game->setMacroInAjaxWindow('html/station/dockControl.twig');
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
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
