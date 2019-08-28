<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSelfDestruct;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowSelfDestruct implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SELFDESTRUCT_AJAX';

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

        // @todo repair
        $code = 'gehtned';

        $game->setPageTitle(_('Selbstzerstörung'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/selfdestruct');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('SELF_DESTRUCT_CODE', $code);
    }
}
