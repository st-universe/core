<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceInfo;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowLicenceInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENCE_INFO';

    private ShowLicenceInfoRequestInterface $showLicenceInfoRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;


    public function __construct(
        ShowLicenceInfoRequestInterface $showLicenceInfoRequest,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenceInfoRequest = $showLicenceInfoRequest;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {

        $trade_post = $this->showLicenceInfoRequest->getTradePostId();
        $commodityId = $this->tradeLicenseRepository->getLicenceGoodIdByTradepost((int) $trade_post);
        $commodityName = $this->commodityRepository->find($commodityId)->getName();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicenceinfo');
        $game->setPageTitle(sprintf(
            _('Lizenzinformation')
        ));
        $game->setTemplateVar('TRADEPOST', $trade_post);
        $game->setTemplateVar('LICENSEGOOD', $commodityId);
        $game->setTemplateVar('LICENSEGOODNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $this->tradeLicenseRepository->getLicenceGoodAmountByTradepost((int) $trade_post));
        $game->setTemplateVar('LICENSEDAYS', $this->tradeLicenseRepository->getLicenceDaysByTradepost((int) $trade_post));
        $game->setTemplateVar('LICENSEDATE', $this->tradeLicenseRepository->getSetDateLicenceByTradepost((int) $trade_post));
    }
}