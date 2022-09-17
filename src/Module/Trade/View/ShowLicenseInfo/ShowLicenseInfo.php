<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;

final class ShowLicenseInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_INFO';

    private ShowLicenseInfoRequestInterface $showLicenseInfoRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository;


    public function __construct(
        ShowLicenseInfoRequestInterface $showLicenseInfoRequest,
        TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenseInfoRequest = $showLicenseInfoRequest;
        $this->TradeLicenseInfoRepository = $TradeLicenseInfoRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {

        $tradePostId = $this->showLicenseInfoRequest->getTradePostId();

        //TODO sanity checks, postId not present? licenseInfo not present?
        $licenseInfo = $this->TradeLicenseInfoRepository->getLatestLicenseInfo($tradePostId);
        $commodityId = $licenseInfo->getGoodsId();
        $commodityName = $this->commodityRepository->find($commodityId)->getName();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicenseinfo');
        $game->setPageTitle(sprintf(
            _('Lizenzinformation')
        ));
        $game->setTemplateVar('TRADEPOST', $tradePostId);
        $game->setTemplateVar('LICENSEGOOD', $commodityId);
        $game->setTemplateVar('LICENSEGOODNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licenseInfo->getAmount());
        $game->setTemplateVar('LICENSEDAYS', $licenseInfo->getDays());
        $game->setTemplateVar('LICENSEDATE', $licenseInfo->getDate());
    }
}
