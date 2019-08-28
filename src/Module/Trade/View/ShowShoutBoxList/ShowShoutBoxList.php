<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use TradeLicences;
use TradeShoutbox;

final class ShowShoutBoxList implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_SHOUTBOX_LIST';

    private $showShoutBoxListRequest;

    public function __construct(
        ShowShoutBoxListRequestInterface $showShoutBoxListRequest
    ) {
        $this->showShoutBoxListRequest = $showShoutBoxListRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxListRequest->getTradeNetworkId();

        if (!TradeLicences::hasLicenceInNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolation();
        }
        $game->showMacro('html/trademacros.xhtml/shoutbox_entries');
        $game->setTemplateVar('SHOUTBOX', TradeShoutbox::getByTradeNetworkId($tradeNetworkId));
    }
}