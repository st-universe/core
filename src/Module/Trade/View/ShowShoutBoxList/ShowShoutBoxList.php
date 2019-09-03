<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use TradeLicences;

final class ShowShoutBoxList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHOUTBOX_LIST';

    private $showShoutBoxListRequest;

    private $tradeShoutboxRepository;

    public function __construct(
        ShowShoutBoxListRequestInterface $showShoutBoxListRequest,
        TradeShoutboxRepositoryInterface $tradeShoutboxRepository
    ) {
        $this->showShoutBoxListRequest = $showShoutBoxListRequest;
        $this->tradeShoutboxRepository = $tradeShoutboxRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxListRequest->getTradeNetworkId();

        if (!TradeLicences::hasLicenceInNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolation();
        }
        $game->showMacro('html/trademacros.xhtml/shoutbox_entries');
        $game->setTemplateVar('SHOUTBOX', $this->tradeShoutboxRepository->getByTradeNetwork($tradeNetworkId));
    }
}