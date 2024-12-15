<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowTradeMenuTransfer;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenuTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRADEMENU_TRANSFER';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $mode = request::getStringFatal('mode');
        match ($mode) {
            'from' => $game->showMacro('html/ship/transferfromaccount.twig'),
            default => $game->showMacro('html/ship/transfertoaccount.twig'),
        };

        $tradepost = $this->tradePostRepository->find(request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($ship, $tradepost->getStation())) {
            throw new AccessViolation();
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountWrapper($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
    }
}
