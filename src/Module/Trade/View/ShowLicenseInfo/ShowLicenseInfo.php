<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowLicenseInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_INFO';

    private ShowLicenseInfoRequestInterface $showLicenseInfoRequest;

    private TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    public function __construct(
        ShowLicenseInfoRequestInterface $showLicenseInfoRequest,
        TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
    ) {
        $this->showLicenseInfoRequest = $showLicenseInfoRequest;
        $this->tradeLicenseInfoRepository = $tradeLicenseInfoRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradePostId = $this->showLicenseInfoRequest->getTradePostId();

        $licenseInfo = $this->tradeLicenseInfoRepository->getLatestLicenseInfo($tradePostId);

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicenseinfo');
        $game->setPageTitle(_('Lizenzinformation'));
        $game->setTemplateVar('LICENSEINFO', $licenseInfo);
        $game->setTemplateVar('CURRENTLICENSE', $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($game->getUser()->getId(), $tradePostId));
    }
}
