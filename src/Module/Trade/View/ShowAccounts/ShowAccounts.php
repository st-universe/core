<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowAccounts;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeAccountWrapperInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowAccounts implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ACCOUNTS';

    public function __construct(private TradeLibFactoryInterface $tradeLibFactory, private TradePostRepositoryInterface $tradePostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $list = array_map(
            fn (TradePost $tradePost): TradeAccountWrapperInterface => $this->tradeLibFactory->createTradeAccountWrapper($tradePost, $userId),
            $this->tradePostRepository->getByUserLicense($userId)
        );

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', self::VIEW_IDENTIFIER),
            _('Warenkonten')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setViewTemplate('html/trade/tradeAccounts.twig');

        $game->setTemplateVar('ACCOUNTS', $list);
    }
}
