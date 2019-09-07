<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenuNewOffer;

use AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class ShowOfferMenuNewOffer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU_NEW_OFFER';

    private $showOfferMenuNewOfferRequest;

    private $commodityRepository;

    private $tradeStorageRepository;

    public function __construct(
        ShowOfferMenuNewOfferRequestInterface $showOfferMenuNewOfferRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->showOfferMenuNewOfferRequest = $showOfferMenuNewOfferRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = $this->tradeStorageRepository->find($this->showOfferMenuNewOfferRequest->getStorageId());
        if ($storage === null || $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $commodityList = $this->commodityRepository->getViewable();
        usort(
            $commodityList,
            function (CommodityInterface $a, CommodityInterface $b): int {
                return $a->getSort() <=> $b->getSort();
            }
        );

        $game->showMacro('html/trademacros.xhtml/newoffermenu_newoffer');
        $game->setPageTitle(sprintf(
            _('Management %s'), $storage->getGood()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_DILITHIUM', (int) $storage->getGoodId() === CommodityTypeEnum::GOOD_DILITHIUM);
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);
    }
}