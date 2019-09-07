<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseList;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use TradePost;

final class ShowLicenseList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_LIST';

    private $showLicenseListRequest;

    private $tradeLicenseRepository;

    public function __construct(
        ShowLicenseListRequestInterface $showLicenseListRequest,
        TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {
        $this->showLicenseListRequest = $showLicenseListRequest;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/tradelicencelist');
        $game->setPageTitle(_('Liste ausgestellter Handelslizenzen'));

        $tradepost = new TradePost($this->showLicenseListRequest->getTradePostId());

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($game->getUser()->getId(), (int) $tradepost->getId())) {
            throw new AccessViolation();
        }
        $game->setTemplateVar('LIST', $this->tradeLicenseRepository->getByTradePost((int) $tradepost->getId()));
    }
}