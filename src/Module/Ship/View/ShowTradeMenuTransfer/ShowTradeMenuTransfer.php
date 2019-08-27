<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuTransfer;

use AccessViolation;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTradeMenuTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_TRANSFER';

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

        $mode = request::getStringFatal('mode');
        switch ($mode) {
            case 'from':
                $game->setAjaxMacro('html/shipmacros.xhtml/transferfromaccount');
                break;
            case 'to':
            default:
                $game->setAjaxMacro('html/shipmacros.xhtml/transfertoaccount');
        }
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($ship, $tradepost->getShip())) {
            new AccessViolation;
        }

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setTemplateVar('TRADEPOST', $tradepost);
        $game->setTemplateVar('SHIP', $ship);
    }
}
