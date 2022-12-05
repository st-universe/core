<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowLicenseInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_INFO';

    private ShowLicenseInfoRequestInterface $showLicenseInfoRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    public function __construct(
        ShowLicenseInfoRequestInterface $showLicenseInfoRequest,
        TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenseInfoRequest = $showLicenseInfoRequest;
        $this->tradeLicenseInfoRepository = $tradeLicenseInfoRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $tradePostId = $this->showLicenseInfoRequest->getTradePostId();

        //TODO sanity checks, postId not present? licenseInfo not present?
        $licenseInfo = $this->tradeLicenseInfoRepository->getLatestLicenseInfo($tradePostId);
        $commodityName = $this->commodityRepository->find($licenseInfo->getCommodityId())->getName();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicenseinfo');
        $game->setPageTitle(sprintf(
            _('Lizenzinformation')
        ));
        $game->setTemplateVar('TRADEPOST', $tradePostId);
        $game->setTemplateVar('LICENSECOMMODITYNAME', $commodityName);
        $game->setTemplateVar('LICENSEINFO', $licenseInfo);
        $game->setTemplateVar('CURRENTLICENSE', $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($game->getUser()->getId(), $tradePostId));
    }
}
