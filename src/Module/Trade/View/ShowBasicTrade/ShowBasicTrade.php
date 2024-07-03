<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowBasicTrade;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\BasicTradeAccountTalInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowBasicTrade implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BASIC_TRADE';

    public function __construct(private BasicTradeRepositoryInterface $basicTradeRepository, private TradePostRepositoryInterface $tradePostRepository, private TradeLibFactoryInterface $tradeLibFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $basicTrades = $this->basicTradeRepository->getBasicTrades($userId);

        $basicTradeAccounts = array_map(
            fn(TradePostInterface $tradePost): BasicTradeAccountTalInterface => $this->tradeLibFactory->createBasicTradeAccountTal($tradePost, $basicTrades, $userId),
            $this->tradePostRepository->getByUserLicenseOnlyNPC($userId)
        );

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Basishandel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/basictrades.xhtml');

        $game->setTemplateVar('ACCOUNTS', $basicTradeAccounts);
    }
}
