<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuTransfer;

use AccessViolation;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;

final class ShowTradeMenuTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_TRANSFER';

    private $shipLoader;

    private $tradeLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLibFactory = $tradeLibFactory;
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
                $game->showMacro('html/shipmacros.xhtml/transferfromaccount');
                break;
            case 'to':
            default:
                $game->showMacro('html/shipmacros.xhtml/transfertoaccount');
        }
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($ship, $tradepost->getShip())) {
            new AccessViolation;
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
    }
}
