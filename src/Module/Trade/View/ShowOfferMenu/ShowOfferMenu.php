<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenu;

use AccessViolation;
use Good;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use TradePost;
use TradeStorage;

final class ShowOfferMenu implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU';

    private $showOfferMenuRequest;

    public function __construct(
        ShowOfferMenuRequestInterface $showOfferMenuRequest
    ) {
        $this->showOfferMenuRequest = $showOfferMenuRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = new TradeStorage($this->showOfferMenuRequest->getStorageId());
        if ((int) $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $trade_post = new TradePost($storage->getTradePostId());

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/tradeoffermenu');
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