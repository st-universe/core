<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceMenu;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ShowLicenceMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENCE_MENU';

    private ShowLicenceMenuRequestInterface $showLicenceMenuRequest;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ShowLicenceMenuRequestInterface $showLicenceMenuRequest,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->showLicenceMenuRequest = $showLicenceMenuRequest;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $trade_post = $this->showLicenceMenuRequest->getTradePostId();

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicencemenu');
        $game->setPageTitle(sprintf(
            _('Lizenzmanagement')
        ));
        $game->setTemplateVar('TRADEPOST', $trade_post);
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);
    }
}
