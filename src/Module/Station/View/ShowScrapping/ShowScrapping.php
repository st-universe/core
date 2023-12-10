<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowScrapping;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowScrapping implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SCRAP_AJAX';

    private ShipLoaderInterface $shipLoader;

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
            $userId,
            false,
            false
        );

        $code = substr(md5($ship->getName()), 0, 6);

        $game->setPageTitle(_('Demontage'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/scrapping');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('SCRAP_CODE', $code);
    }
}
