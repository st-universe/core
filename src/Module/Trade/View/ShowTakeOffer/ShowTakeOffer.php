<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class ShowTakeOffer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TAKE_OFFER';

    private ShowTakeOfferRequestInterface $showTakeOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    public function __construct(
        ShowTakeOfferRequestInterface $showTakeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository
    ) {
        $this->showTakeOfferRequest = $showTakeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedOffer = $this->tradeOfferRepository->find($this->showTakeOfferRequest->getOfferId());

        if ($selectedOffer === null || !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost(
            $game->getUser()->getId(),
            $selectedOffer->getTradePostId()
        )) {
            throw new AccessViolation();
        }

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/takeoffer');
        $game->setPageTitle(_('Angebot annehmen'));

        $game->setTemplateVar(
            'SELECTED_OFFER',
            $selectedOffer
        );
        $game->setTemplateVar(
            'TRADE_POST',
            $this->tradeLibFactory->createTradeAccountTal($selectedOffer->getTradePost(), $game->getUser()->getId())
        );
    }
}
