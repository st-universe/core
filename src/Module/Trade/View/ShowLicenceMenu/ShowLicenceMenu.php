<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLicenceMenu;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeCreateLicenceRepositoryInterface;

final class ShowLicenceMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LICENCE_MENU';


    private ShowLicenceMenuRequestInterface $showLicenceMenuRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository;


    public function __construct(
        ShowLicenceMenuRequestInterface $showLicenceMenuRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository,

    ) {
        $this->showLicenceMenuRequest = $showLicenceMenuRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeCreateLicenceRepository = $tradeCreateLicenceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();


        $trade_post = $this->tradeCreateLicenceRepository->find($this->showLicenceMenuRequest->getTradePostId());
        if ($trade_post === null) {
            return;
        }

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradelicencemenu');
        $game->setPageTitle(sprintf(
            _('Lizenzmanagement')
        ));
        $game->setTemplateVar('TRADEPOST', $trade_post);
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);
    }
}