<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;

final class ShowShoutBoxList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHOUTBOX_LIST';

    private ShowShoutBoxListRequestInterface $showShoutBoxListRequest;

    private TradeShoutboxRepositoryInterface $tradeShoutboxRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    public function __construct(
        ShowShoutBoxListRequestInterface $showShoutBoxListRequest,
        TradeShoutboxRepositoryInterface $tradeShoutboxRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {
        $this->showShoutBoxListRequest = $showShoutBoxListRequest;
        $this->tradeShoutboxRepository = $tradeShoutboxRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxListRequest->getTradeNetworkId();

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolation();
        }
        $game->showMacro('html/trademacros.xhtml/shoutbox_entries');
        $game->setTemplateVar('SHOUTBOX', $this->tradeShoutboxRepository->getByTradeNetwork($tradeNetworkId));
    }
}
