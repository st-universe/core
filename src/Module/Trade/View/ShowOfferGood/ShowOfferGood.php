<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferGood;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradeOffer;
use TradePost;

final class ShowOfferGood implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_OFFER_GOOD';

    private $showOfferGoodRequest;

    public function __construct(
        ShowOfferGoodRequestInterface $showOfferGoodRequest
    ) {
        $this->showOfferGoodRequest = $showOfferGoodRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $good = ResourceCache()->getObject('good', $this->showOfferGoodRequest->getGoodId());

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/offerbygood');
        $game->setPageTitle(sprintf(_('Angebote mit %d'), $good->getName()));

        $tradepost = new TradePost($this->showOfferGoodRequest->getTradePostId());

        $game->setTemplateVar(
            'OFFER',
            TradeOffer::getOfferByGood($tradepost->getId(), $game->getUser()->getId(), $good->getId())
        );
    }
}