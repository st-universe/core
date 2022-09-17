<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseMenu;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ShowLicenseMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENSE_MENU';

    private ShowLicenseMenuRequestInterface $showLicenseMenuRequest;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ShowLicenseMenuRequestInterface $showLicenseMenuRequest,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenseMenuRequest = $showLicenseMenuRequest;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $trade_post = $this->showLicenseMenuRequest->getTradePostId();

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicensemenu');
        $game->setPageTitle(sprintf(
            _('Lizenzmanagement')
        ));
        $game->setTemplateVar('TRADEPOST', $trade_post);
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);
    }
}
