<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowColonization;

use Colony;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowColonization implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONIZATION';

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

        $colonyId = request::getIntFatal('colid');
        $colony = new Colony($colonyId);
        // @todo add sanity checks

        $game->setPageTitle(_('Kolonie gründen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/colonization');

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
    }
}
