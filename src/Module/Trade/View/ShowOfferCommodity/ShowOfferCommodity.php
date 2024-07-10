<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferCommodity;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowOfferCommodity implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_OFFER_COMMODITY';

    public function __construct(private ShowOfferCommodityRequestInterface $showOfferCommodityRequest, private TradePostRepositoryInterface $tradePostRepository, private TradeOfferRepositoryInterface $tradeOfferRepository, private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $commodity = $this->commodityRepository->find($this->showOfferCommodityRequest->getCommodityId());
        $tradepost = $this->tradePostRepository->find($this->showOfferCommodityRequest->getTradePostId());

        if ($commodity === null || $tradepost === null) {
            return;
        }

        $game->setMacroInAjaxWindow('html/trade/offerByCommodity.twig');
        $game->setPageTitle(sprintf(_('Angebote mit %s'), $commodity->getName()));

        $game->setTemplateVar(
            'OFFER',
            $this->tradeOfferRepository->getByTradePostAndUserAndOfferedCommodity(
                $tradepost->getId(),
                $game->getUser()->getId(),
                $commodity->getId()
            )
        );

        $game->setTemplateVar('VIEW', ShowAccounts::VIEW_IDENTIFIER);
    }
}
