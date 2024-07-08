<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowLicenseInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_LICENSE_INFO';

    public function __construct(private ShowLicenseInfoRequestInterface $showLicenseInfoRequest, private TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $tradePostId = $this->showLicenseInfoRequest->getTradePostId();

        $licenseInfo = $this->tradeLicenseInfoRepository->getLatestLicenseInfo($tradePostId);

        $game->setMacroInAjaxWindow('html/trade/license/info.twig');
        $game->setPageTitle(_('Lizenzinformation'));
        $game->setTemplateVar('LICENSEINFO', $licenseInfo);
        $game->setTemplateVar('CURRENTLICENSE', $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($game->getUser()->getId(), $tradePostId));
    }
}
