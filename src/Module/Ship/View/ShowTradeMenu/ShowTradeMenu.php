<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenu;

use AccessViolation;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTradeMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU';

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

        $tradepost = ResourceCache()->getObject('tradepost', request::indInt('postid'));

        if (!checkPosition($ship, $tradepost->getShip())) {
            new AccessViolation();
        }

        $game->setPageTitle(sprintf(_('Handelsposten: %s'), $tradepost->getName()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/shipmacros.xhtml/trademenu');

        $databaseEntryId = $tradepost->getShip()->getDatabaseId();

        if ($databaseEntryId > 0) {
            $game->checkDatabaseItem($databaseEntryId);
        }

        $game->setTemplateVar('TRADEPOST', $tradepost);
        $game->setTemplateVar('SHIP', $ship);
    }
}
