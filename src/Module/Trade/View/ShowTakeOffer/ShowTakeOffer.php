<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use TradeOffer;

final class ShowTakeOffer implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TAKE_OFFER';

    private $showTakeOfferRequest;

    private $tradeLibFactory;

    private $tradeLicenseRepository;

    public function __construct(
        ShowTakeOfferRequestInterface $showTakeOfferRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {
        $this->showTakeOfferRequest = $showTakeOfferRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedOffer = new TradeOffer($this->showTakeOfferRequest->getOfferId());

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost(
            $game->getUser()->getId(),
            (int) $selectedOffer->getTradePostId()
        )) {
            throw new \AccessViolation();
        }

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/takeoffer');
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