<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferMenu;

use Stu\Exception\AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class ShowOfferMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OFFER_MENU';

    private ShowOfferMenuRequestInterface $showOfferMenuRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    public function __construct(
        ShowOfferMenuRequestInterface $showOfferMenuRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->showOfferMenuRequest = $showOfferMenuRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $storage = $this->tradeStorageRepository->find($this->showOfferMenuRequest->getStorageId());
        if ($storage === null || $storage->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $commodityList = $this->commodityRepository->getTradeable();

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradeoffermenu');
        $game->setPageTitle(sprintf(
            _('Management %s'),
            $storage->getGood()->getName()
        ));
        $game->setTemplateVar('STOR', $storage);
        $game->setTemplateVar('IS_LATINUM', (int) $storage->getGoodId() === CommodityTypeEnum::GOOD_LATINUM);
        $game->setTemplateVar('IS_NPC_POST', (int) $storage->getTradePostId() < 30);
        $game->setTemplateVar('SELECTABLE_GOODS', $commodityList);
    }
}