<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenuNewOffer;

use AccessViolation;
use Good;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use TradePost;
use TradeStorage;

final class ShowOfferMenuNewOffer implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU_NEW_OFFER';

    private $showOfferMenuNewOfferRequest;

    public function __construct(
        ShowOfferMenuNewOfferRequestInterface $showOfferMenuNewOfferRequest
    ) {
        $this->showOfferMenuNewOfferRequest = $showOfferMenuNewOfferRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = new TradeStorage($this->showOfferMenuNewOfferRequest->getStorageId());
        if ((int) $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $trade_post = new TradePost($storage->getTradePostId());

        $game->showMacro('html/trademacros.xhtml/newoffermenu_newoffer');
        $game->setPageTitle(sprintf(
            _('Management %s'), $storage->getGood()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_DILITHIUM', (int) $storage->getGoodId() === GOOD_DILITHIUM);
        $game->setTemplateVar(
            'SELECTABLE_GOODS',
            Good::getGoodsBy(sprintf(
                'WHERE view=1 AND tradeable=1 AND illegal_%d=0 ORDER BY sort',
                $trade_post->getTradeNetwork()
            ))
        );
    }
}