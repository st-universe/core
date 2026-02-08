<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowBasicTrade;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\BasicTradeAccountWrapperInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowBasicTrade implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BASIC_TRADE';

    public function __construct(private BasicTradeRepositoryInterface $basicTradeRepository, private TradePostRepositoryInterface $tradePostRepository, private TradeLibFactoryInterface $tradeLibFactory) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $basicTrades = $this->basicTradeRepository->getBasicTrades($userId);

        $basicTradeAccounts = array_map(
            fn (TradePost $tradePost): BasicTradeAccountWrapperInterface => $this->tradeLibFactory->createBasicTradeAccountWrapper($tradePost, $basicTrades, $userId),
            $this->tradePostRepository->getByUserLicenseOnlyNPC($userId)
        );

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', self::VIEW_IDENTIFIER),
            _('Basishandel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setViewTemplate('html/trade/basictrades.twig');

        $game->setTemplateVar('ACCOUNTS', $basicTradeAccounts);
    }
}
