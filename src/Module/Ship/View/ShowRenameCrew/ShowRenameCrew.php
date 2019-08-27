<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRenameCrew;

use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowRenameCrew implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RENAME_CREW';

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

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/shipmacros.xhtml/crewslot');

        $game->setTemplateVar('SHIP', $ship);
    }
}
