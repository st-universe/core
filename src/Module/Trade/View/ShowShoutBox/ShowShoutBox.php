<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBox;

use AccessViolation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use TradeLicences;
use TradeShoutbox;

final class ShowShoutBox implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_SHOUTBOX';

    private $showShoutBoxRequest;

    public function __construct(
        ShowShoutBoxRequestInterface $showShoutBoxRequest
    ) {
        $this->showShoutBoxRequest = $showShoutBoxRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxRequest->getTradeNetworkId();

        if (!TradeLicences::hasLicenceInNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolation();
        }

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/trademacros.xhtml/shoutbox');
        $game->setPageTitle(_('Schwarzes Brett'));
        $game->setTemplateVar('NETWORK', $tradeNetworkId);
        $game->setTemplateVar('SHOUTBOX', TradeShoutbox::getByTradeNetworkId($tradeNetworkId));
    }
}