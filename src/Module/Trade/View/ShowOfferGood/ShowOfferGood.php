<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferGood;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowOfferGood implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_GOOD';

    private $showOfferGoodRequest;

    private $tradePostRepository;

    private $tradeOfferRepository;

    public function __construct(
        ShowOfferGoodRequestInterface $showOfferGoodRequest,
        TradePostRepositoryInterface $tradePostRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository
    ) {
        $this->showOfferGoodRequest = $showOfferGoodRequest;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
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
            $this->tradeOfferRepository->getByTradePostAndUserAndOfferedCommodity(
                $tradepost->getId(),
                $game->getUser()->getId(),
                (int) $good->getId()
            )
        );
    }
}