<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferGood;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowOfferGood implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_GOOD';

    private ShowOfferGoodRequestInterface $showOfferGoodRequest;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ShowOfferGoodRequestInterface $showOfferGoodRequest,
        TradePostRepositoryInterface $tradePostRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showOfferGoodRequest = $showOfferGoodRequest;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $commodity = $this->commodityRepository->find($this->showOfferGoodRequest->getGoodId());
        $tradepost = $this->tradePostRepository->find($this->showOfferGoodRequest->getTradePostId());

        if ($commodity === null || $tradepost === null) {
            return;
        }

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/offerbygood');
        $game->setPageTitle(sprintf(_('Angebote mit %d'), $commodity->getName()));

        $game->setTemplateVar(
            'OFFER',
            $this->tradeOfferRepository->getByTradePostAndUserAndOfferedCommodity(
                $tradepost->getId(),
                $game->getUser()->getId(),
                (int)$commodity->getId()
            )
        );
    }
}
