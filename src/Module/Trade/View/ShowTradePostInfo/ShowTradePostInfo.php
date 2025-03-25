<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradePostInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRADEPOST_INFO';

    public function __construct(private ShowTradePostInfoRequestInterface $showTradePostInfoRequest, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeLibFactoryInterface $talFactory, private TradePostRepositoryInterface $tradePostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $tradePost = $this->tradePostRepository->find($this->showTradePostInfoRequest->getTradePostId());
        if ($tradePost === null) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradePost->getId())) {
            throw new AccessViolationException();
        }

        $game->setMacroInAjaxWindow('html/trade/tradePostInfo.twig');
        $game->setPageTitle(_('Handelsposten Details'));

        $game->setTemplateVar('IS_NPC_TRADEPOST', $tradePost->isNpcTradepost());
        $game->setTemplateVar(
            'TRADE_POST_INFO',
            $this->talFactory->createTradeAccountWrapper($tradePost, $userId)
        );
    }
}
