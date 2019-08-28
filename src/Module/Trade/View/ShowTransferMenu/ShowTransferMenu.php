<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTransferMenu;

use AccessViolation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradePost;
use TradeStorage;

final class ShowTransferMenu implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU_TRANSFER';

    private $showTransferMenueRequest;

    public function __construct(
        ShowTransferMenueRequestInterface $showTransferMenueRequest
    ) {
        $this->showTransferMenueRequest = $showTransferMenueRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = new TradeStorage($this->showTransferMenueRequest->getStorageId());
        if ((int) $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }
        $trade_post = new TradePost($storage->getTradePostId());
        $accounts = TradePost::getListByLicences($userId);

        $trade_post_list = [];
        foreach ($accounts as $key => $obj) {
            if ($trade_post->getId() != $obj->getId() && $obj->getTradeNetwork() == $trade_post->getTradeNetwork()) {
                $trade_post_list[] = $obj;
            }
        }

        $game->showMacro('html/trademacros.xhtml/newoffermenu_transfer');
        $game->setPageTitle(sprintf(
            _('Management %s'), $storage->getGood()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_DILITHIUM', $storage->getGoodId() === GOOD_DILITHIUM);
        $game->setTemplateVar(
            'TRADE_POST',
            $trade_post
        );
        $game->setTemplateVar(
            'AVAILABLE_TRADE_POSTS',
            $trade_post_list
        );
    }
}