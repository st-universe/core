<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseList;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use TradeLicences;
use TradePost;

final class ShowLicenseList implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_LIST';

    private $showLicenseListRequest;

    public function __construct(
        ShowLicenseListRequestInterface $showLicenseListRequest
    ) {
        $this->showLicenseListRequest = $showLicenseListRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/trademacros.xhtml/tradelicencelist');
        $game->setPageTitle(_('Liste ausgestellter Handelslizenzen'));

        $tradepost = new TradePost($this->showLicenseListRequest->getTradePostId());

        if (!$tradepost->userHasLicence($game->getUser()->getId())) {
            throw new AccessViolation();
        }
        $game->setTemplateVar('LIST', TradeLicences::getLicencesByTradePost($tradepost->getId()));
    }
}