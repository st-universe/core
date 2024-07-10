<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseList;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowLicenseList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_LICENSE_LIST';

    public function __construct(private ShowLicenseListRequestInterface $showLicenseListRequest, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradePostRepositoryInterface $tradePostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setMacroInAjaxWindow('html/trade/license/list.twig');
        $game->setPageTitle(_('Liste ausgestellter Handelslizenzen'));

        $tradepost = $this->tradePostRepository->find($this->showLicenseListRequest->getTradePostId());
        if ($tradepost === null) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($game->getUser()->getId(), $tradepost->getId())) {
            throw new AccessViolation();
        }
        $game->setTemplateVar('LIST', $this->tradeLicenseRepository->getByTradePostAndNotExpired($tradepost->getId()));
    }
}
