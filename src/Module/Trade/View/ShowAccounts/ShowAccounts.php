<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowAccounts;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeAccountTalInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowAccounts implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ACCOUNTS';

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $list = array_map(
            fn(TradePostInterface $tradePost): TradeAccountTalInterface => $this->tradeLibFactory->createTradeAccountTal($tradePost, $userId),
            $this->tradePostRepository->getByUserLicense($userId)
        );

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Warenkonten')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/tradeaccounts.xhtml');

        $game->setTemplateVar('ACCOUNTS', $list);
    }
}
