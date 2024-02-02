<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenu;

use Stu\Exception\AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShowOfferMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU';

    private ShowOfferMenuRequestInterface $showOfferMenuRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        ShowOfferMenuRequestInterface $showOfferMenuRequest,
        CommodityRepositoryInterface $commodityRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->showOfferMenuRequest = $showOfferMenuRequest;
        $this->commodityRepository = $commodityRepository;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = $this->storageRepository->find($this->showOfferMenuRequest->getStorageId());
        if ($storage === null || $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $tradepost = $storage->getTradePost();
        if ($tradepost === null) {
            throw new AccessViolation();
        }

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradeoffermenu');
        $game->setPageTitle(sprintf(
            _('Management %s'),
            $storage->getCommodity()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_LATINUM', $storage->getCommodityId() === CommodityTypeEnum::COMMODITY_LATINUM);
        $game->setTemplateVar('IS_NPC_POST', $tradepost->getUser()->isNpc());
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);
    }
}
