<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;

final class ShowShoutBoxList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHOUTBOX_LIST';

    public function __construct(private ShowShoutBoxListRequestInterface $showShoutBoxListRequest, private TradeShoutboxRepositoryInterface $tradeShoutboxRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxListRequest->getTradeNetworkId();

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolationException();
        }
        $game->showMacro('html/trade/shoutBoxEntries.twig');
        $game->setTemplateVar('SHOUTBOX', $this->tradeShoutboxRepository->getByTradeNetwork($tradeNetworkId));
    }
}
