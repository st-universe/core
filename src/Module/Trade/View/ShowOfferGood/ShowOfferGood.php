<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferGood;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use TradeOffer;

final class ShowOfferGood implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_GOOD';

    private $showOfferGoodRequest;

    private $tradePostRepository;

    public function __construct(
        ShowOfferGoodRequestInterface $showOfferGoodRequest,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->showOfferGoodRequest = $showOfferGoodRequest;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $good = ResourceCache()->getObject('good', $this->showOfferGoodRequest->getGoodId());

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/offerbygood');
        $game->setPageTitle(sprintf(_('Angebote mit %d'), $good->getName()));

        $tradepost = $this->tradePostRepository->find($this->showOfferGoodRequest->getTradePostId());
        if ($tradepost === null) {
            return;;
        }

        $game->setTemplateVar(
            'OFFER',
            TradeOffer::getOfferByGood($tradepost->getId(), $game->getUser()->getId(), $good->getId())
        );
    }
}