<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenuNewOffer;

use Stu\Exception\AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShowOfferMenuNewOffer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU_NEW_OFFER';

    private ShowOfferMenuNewOfferRequestInterface $showOfferMenuNewOfferRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        ShowOfferMenuNewOfferRequestInterface $showOfferMenuNewOfferRequest,
        CommodityRepositoryInterface $commodityRepository,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->showOfferMenuNewOfferRequest = $showOfferMenuNewOfferRequest;
        $this->commodityRepository = $commodityRepository;
        $this->storageRepository = $storageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = $this->storageRepository->find($this->showOfferMenuNewOfferRequest->getStorageId());
        if ($storage === null || $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $commodityList = $this->commodityRepository->getTradeable();

        $game->showMacro('html/trademacros.xhtml/newoffermenu_newoffer');
        $game->setPageTitle(sprintf(
            _('Management %s'),
            $storage->getCommodity()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_LATINUM', $storage->getCommodityId() === CommodityTypeEnum::COMMODITY_LATINUM);
        $game->setTemplateVar('IS_NPC_POST', $storage->getTradePost()->getId() < 18);
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);
    }
}
