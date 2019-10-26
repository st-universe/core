<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuTransfer;

use AccessViolation;
use request;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenuTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_TRANSFER';

    private $shipLoader;

    private $tradeLibFactory;

    private $tradePostRepository;

    private $positionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->positionChecker = $positionChecker;
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
        /** @var TradePostInterface $tradepost */
        $tradepost = $this->tradePostRepository->find((int) request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->positionChecker->checkPosition($ship, $tradepost->getShip())) {
            new AccessViolation;
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
    }
}
