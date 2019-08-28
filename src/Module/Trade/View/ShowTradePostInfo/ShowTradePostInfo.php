<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use TradePost;

final class ShowTradePostInfo implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TRADEPOST_INFO';

    private $showTradePostInfoRequest;

    public function __construct(
        ShowTradePostInfoRequestInterface $showTradePostInfoRequest
    ) {
        $this->showTradePostInfoRequest = $showTradePostInfoRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/tradepostinfo');
        $game->setPageTitle(_('Handelsposten Details'));

        // @todo check if user may access trade post

        $trade_post = new TradePost($this->showTradePostInfoRequest->getTradePostId());

        $game->setTemplateVar(
            'TRADE_POST',
            $trade_post
        );
    }
}