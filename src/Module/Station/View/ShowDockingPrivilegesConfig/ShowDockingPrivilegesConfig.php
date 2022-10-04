<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingPrivilegesConfig;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class ShowDockingPrivilegesConfig implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_CONFIG';

    private ShipLoaderInterface $shipLoader;
    private AllianceRepositoryInterface $allianceRepository;

    public function __construct(
        AllianceRepositoryInterface $allianceRepository,
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Dockrechte'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/dockprivileges');
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
        $game->setTemplateVar('SHIP', $ship);
    }
}