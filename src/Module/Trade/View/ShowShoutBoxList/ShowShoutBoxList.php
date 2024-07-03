<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use Override;
use Stu\Exception\AccessViolation;
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

    #[Override]
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
