<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBox;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;

final class ShowShoutBox implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHOUTBOX';

    public function __construct(private ShowShoutBoxRequestInterface $showShoutBoxRequest, private TradeShoutboxRepositoryInterface $tradeShoutboxRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $tradeNetworkId = $this->showShoutBoxRequest->getTradeNetworkId();

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndNetwork($game->getUser()->getId(), $tradeNetworkId)) {
            throw new AccessViolationException();
        }

        $game->setMacroInAjaxWindow('html/trade/shoutbox.twig');
        $game->setPageTitle(_('Schwarzes Brett'));
        $game->setTemplateVar('NETWORK', $tradeNetworkId);
        $game->setTemplateVar('SHOUTBOX', $this->tradeShoutboxRepository->getByTradeNetwork($tradeNetworkId));
    }
}
