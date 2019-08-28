<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowDockingPrivilegesConfig;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowDockingPrivilegesConfig implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_CONFIG';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Dockrechte'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/dockprivileges');

        $game->setTemplateVar('SHIP', $ship);
    }
}
