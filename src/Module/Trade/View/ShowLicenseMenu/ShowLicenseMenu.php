<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenseMenu;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ShowLicenseMenu implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_LICENSE_MENU';

    public function __construct(private ShowLicenseMenuRequestInterface $showLicenseMenuRequest, private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $trade_post = $this->showLicenseMenuRequest->getTradePostId();

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicensemenu');
        $game->setPageTitle(_('Lizenzmanagement'));
        $game->setTemplateVar('TRADEPOST', $trade_post);
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);
    }
}
