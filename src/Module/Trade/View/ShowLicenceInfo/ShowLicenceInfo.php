<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;

final class ShowLicenceInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENCE_INFO';

    private ShowLicenceInfoRequestInterface $showLicenceInfoRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository;


    public function __construct(
        ShowLicenceInfoRequestInterface $showLicenceInfoRequest,
        TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenceInfoRequest = $showLicenceInfoRequest;
        $this->TradeLicenseInfoRepository = $TradeLicenseInfoRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {

        $tradePostId = $this->showLicenceInfoRequest->getTradePostId();

        //TODO sanity checks, postId not present? licenseInfo not present?
        $licenseInfo = $this->TradeLicenseInfoRepository->getLatestLicenseInfo($tradePostId);
        $commodityId = $licenseInfo->getGoodsId();
        $commodityName = $this->commodityRepository->find($commodityId)->getName();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicenceinfo');
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
